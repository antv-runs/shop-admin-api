<?php

namespace App\Jobs;

use App\Mail\ProductExportFailedMail;
use App\Mail\ProductExportNoDataMail;
use App\Mail\ProductExportReadyMail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;

class ExportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;
    public array $filters;
    public string $format;

    public function __construct(int $userId, array $filters = [], string $format = 'csv')
    {
        $this->userId = $userId;
        $this->filters = $filters;
        $this->format = $format;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Build the query with filters
            $query = Product::with('category');

            // Apply filters
            if (!empty($this->filters['search'])) {
                $search = $this->filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            if (!empty($this->filters['category_id'])) {
                $query->where('category_id', $this->filters['category_id']);
            }

            // Handle status filter
            $status = $this->filters['status'] ?? 'active';
            if ($status === 'deleted') {
                $query = Product::onlyTrashed();
            } elseif ($status === 'all') {
                $query = Product::withTrashed();
            }

            // Get all matching products
            $products = $query->latest('id')->get();

            if ($products->isEmpty()) {
                $this->notifyNoData();
                return;
            }

            // Generate file
            $filename = $this->generateFileName();
            $filePath = $this->exportToFile($products, $filename);

            // Generate download URL
            $downloadUrl = config('app.url') . '/api/products/exports/' . $filename;

            // Get user and send notification
            $user = User::find($this->userId);
            if ($user && $user->email) {
                // Send email with download link
                Mail::to($user->email)
                    ->send(new ProductExportReadyMail(
                        user: $user,
                        downloadUrl: $downloadUrl,
                        filename: $filename,
                        format: $this->format,
                        productCount: $products->count(),
                    ));
            }

            // Log success
            Log::info("Product export completed", [
                'user_id' => $this->userId,
                'file' => $filename,
                'count' => $products->count(),
                'format' => $this->format,
            ]);

        } catch (\Exception $e) {
            Log::error("Product export failed", [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);

            // Notify user of failure
            $user = User::find($this->userId);
            if ($user && $user->email) {
                Mail::to($user->email)
                    ->send(new ProductExportFailedMail(
                        user: $user,
                        errorMessage: $e->getMessage(),
                    ));
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ExportProductsJob permanently failed', [
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Export products to CSV or Excel file
     *
     * @param mixed $products
     * @param string $filename
     * @return string File path in storage
     */
    protected function exportToFile($products, string $filename): string
    {
        $disk = config('filesystems.default');
        $filePath = "exports/{$filename}";

        // Ensure directory exists
        if (!Storage::disk($disk)->exists('exports')) {
            Storage::disk($disk)->makeDirectory('exports');
        }

        if ($this->format === 'excel') {
            return $this->exportToExcel($products, $filePath);
        }

        return $this->exportToCsv($products, $filePath);
    }

    /**
     * Export products to CSV file
     *
     * @param mixed $products
     * @param string $filePath
     * @return string
     */
    protected function exportToCsv($products, string $filePath): string
    {
        $file = fopen('php://temp', 'r+');

        // Write headers
        $headers = ['ID', 'Name', 'Slug', 'Price', 'Category', 'Description', 'Image', 'Created At'];
        fputcsv($file, $headers);

        // Write data
        foreach ($products as $product) {
            fputcsv($file, [
                $product->id,
                $product->name,
                $product->slug,
                $product->price,
                $product->category?->name ?? 'N/A',
                $product->description,
                $product->image ? asset("storage/{$product->image}") : 'N/A',
                $product->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        // Reset cursor to beginning
        rewind($file);

        // Get content and close temporary file
        $content = stream_get_contents($file);
        fclose($file);

        // Store using configured default disk (may be minio)
        $disk = config('filesystems.default');
        Storage::disk($disk)->put($filePath, $content);

        return $filePath;
    }

    /**
     * Export products to Excel file
     * For basic implementation, we use CSV format
     * To use true Excel, install maatwebsite/excel package
     *
     * @param mixed $products
     * @param string $filePath
     * @return string
     */
    protected function exportToExcel($products, string $filePath): string
    {
        $filePath = str_replace('.csv', '.xlsx', $filePath);

        $disk = config('filesystems.default');

        Excel::store(
            new ProductsExport($products),
            $filePath,
            $disk
        );

        return $filePath;
    }

    /**
     * Generate unique filename with timestamp
     */
    protected function generateFileName(): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $extension = $this->format === 'excel' ? 'xlsx' : 'csv';

        return "products_export_{$timestamp}.{$extension}";
    }

    protected function notifyNoData(): void
    {
        $user = User::find($this->userId);

        if ($user && $user->email) {
            Mail::to($user->email)
                ->send(new ProductExportNoDataMail(
                    user: $user,
                    format: $this->format
                ));
        }

        Log::info('Product export returned empty result', [
            'user_id' => $this->userId,
            'filters' => $this->filters,
            'format' => $this->format,
        ]);
    }
}
