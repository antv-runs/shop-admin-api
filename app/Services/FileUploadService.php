<?php

namespace App\Services;

use App\Contracts\FileUploadServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Handle file upload operations
 *
 * Single Responsibility: only handles file uploads
 * Open/Closed: easily extended for different upload types
 * Dependency Inversion: implements FileUploadServiceInterface
 */
class FileUploadService implements FileUploadServiceInterface
{
    /**
     * Upload a product image and return the path
     */
    public function uploadProductImage(UploadedFile $file): string
    {
        return $file->store('products', 'public');
    }

    /**
     * Delete a file from public storage
     */
    public function deleteFile(string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            try {
                Storage::disk('public')->delete($path);
            } catch (\Throwable $e) {
                // Log or handle silently
            }
        }
    }

    /**
     * Replace old file with new one
     */
    public function replaceFile(?string $oldPath, UploadedFile $newFile): string
    {
        if ($oldPath) {
            $this->deleteFile($oldPath);
        }
        return $this->uploadProductImage($newFile);
    }
}
