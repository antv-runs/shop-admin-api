<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ProductRequest;
use App\Http\Requests\ExportProductRequest;
use App\Http\Resources\ProductResource;
use App\Contracts\ProductServiceInterface;
use App\Contracts\FileUploadServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends BaseController
{
    private ProductServiceInterface $productService;
    private FileUploadServiceInterface $fileUploadService;

    public function __construct(ProductServiceInterface $productService, FileUploadServiceInterface $fileUploadService)
    {
        $this->productService = $productService;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Get all products with pagination, search and category filtering
     *
     * @OA\Get(
     *     path="/api/products",
     *     summary="List all products",
     *     description="Get paginated list of products with optional search and category filtering",
     *     tags={"Products"},
     *     @OA\Parameter(name="search", in="query", description="Search by product name", @OA\Schema(type="string")),
     *     @OA\Parameter(name="category_id", in="query", description="Filter by category ID", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", description="Page number", @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Products retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $products = $this->productService->getAllProducts($request, $perPage);
        return $this->success(
            ProductResource::collection($products),
            'Products retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create new product",
     *     description="Create a new product (Admin only). Validation: name required max:255, price required numeric min:0, category_id nullable exists:categories, image nullable mimes:jpeg,png,jpg,gif max:2048",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Product data per ProductRequest validation",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","price"},
     *                 @OA\Property(property="name", type="string", maxLength=255),
     *                 @OA\Property(property="price", type="number", format="float", minimum=0),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="image", type="string", format="binary", description="Image file (jpeg, png, jpg, gif, max 2MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(ProductRequest $request)
    {
        $data = $request->validated();

        // Handle image upload - delegated to FileUploadService
        if ($request->hasFile('image')) {
            $data['image'] = $this->fileUploadService->uploadProductImage($request->file('image'));
        }

        $product = $this->productService->createProduct($data);

        return $this->success(
            new ProductResource($product['data']),
            'Product created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Get a specific product detail by ID or slug
     *
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get product detail",
     *     description="Retrieve a single product by ID or slug",
     *     tags={"Products"},
     *     @OA\Parameter(name="id", in="path", required=true, description="Product ID or slug", @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Product retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function show($id)
    {
        $product = $this->productService->getProduct($id);
        return $this->success(
            new ProductResource($product),
            'Product retrieved successfully'
        );
    }

    /**
     * @OA\Patch(
     *     path="/api/products/{id}",
     *     summary="Update product",
     *     description="Update product details (Admin only). Validation same as create",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Product ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Product data per ProductRequest validation",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","price"},
     *                 @OA\Property(property="name", type="string", maxLength=255),
     *                 @OA\Property(property="price", type="number", format="float", minimum=0),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required"),
     *     @OA\Response(response=404, description="Product not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(ProductRequest $request, $id)
    {
        $product = $this->productService->getProduct($id);
        $data = $request->validated();

        // Handle image upload - delegated to FileUploadService
        if ($request->hasFile('image')) {
            $data['image'] = $this->fileUploadService->replaceFile($product->image, $request->file('image'));
        }

        $result = $this->productService->updateProduct($product, $data);

        return $this->success(
            new ProductResource($result['data'] ?? $product),
            'Product updated successfully'
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Soft delete product",
     *     description="Soft delete a product (Admin only)",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Product ID", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function destroy($id)
    {
        $this->productService->deleteProduct($id);

        return $this->success(
            null,
            'Product deleted successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/products/trashed",
     *     summary="Get soft deleted products",
     *     description="Get soft deleted products with pagination (Admin only)",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", description="Page number", @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", @OA\Schema(type="integer", default=10)),
     *     @OA\Response(
     *         response=200,
     *         description="Trashed products retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required")
     * )
     */
    public function trashed(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);

        $products = $this->productService->getTrashed($perPage);

        return $this->success(
            $products,
            'Trashed products retrieved successfully'
        );
    }

    /**
     * @OA\Patch(
     *     path="/api/products/{id}/restore",
     *     summary="Restore soft deleted product",
     *     description="Restore a soft deleted product (Admin only)",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Product ID", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Product restored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function restore($id)
    {
        $product = $this->productService->restoreProduct($id);
        return $this->success(
            new ProductResource($product),
            'Product restored successfully'
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}/force-delete",
     *     summary="Permanently delete product",
     *     description="Permanently delete a product (cannot be restored, Admin only)",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Product ID", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Product permanently deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function forceDelete($id)
    {
        $this->productService->forceDeleteProduct($id);
        return $this->success(
            null,
            'Product permanently deleted'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/products/export",
     *     summary="Export products to file",
     *     description="Export products to CSV or Excel format with optional filtering (Admin only, async job, returns 202 Accepted)",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Export parameters per ExportProductRequest validation",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"format"},
     *                 @OA\Property(property="format", type="string", enum={"csv", "excel"}, description="Export file format"),
     *                 @OA\Property(property="search", type="string", description="Optional search term"),
     *                 @OA\Property(property="category_id", type="integer", description="Optional category filter"),
     *                 @OA\Property(property="status", type="string", enum={"active", "deleted", "all"}, description="Filter by status")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Export job queued",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Export job queued. You will receive an email with the download link shortly."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="format", type="string", example="excel")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function export(ExportProductRequest $request)
    {
        $data = $request->validated();

        // Get current authenticated user
        $user = auth()->user();

        // Dispatch export job
        $this->productService->exportProducts(
            $user->id,
            [
                'search' => $data['search'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'status' => $data['status'] ?? 'active',
            ],
            $data['format']
        );

        return $this->success(
            ['format' => $request->input('format')],
            'Export job queued. You will receive an email with the download link shortly.',
            Response::HTTP_ACCEPTED
        );
    }

    /**
     * Download exported file
     *
     * @OA\Get(
     *     path="/api/products/exports/{filename}",
     *     summary="Download exported product file",
     *     description="Download an exported product file (CSV or XLSX). Can be accessed via signed URL from email or with Bearer token.",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="filename",
     *         in="path",
     *         required=true,
     *         description="Filename of the export",
     *         @OA\Schema(type="string", example="products_export_2026-03-03_09-41-08.xlsx")
     *     ),
     *     @OA\Parameter(
     *         name="signature",
     *         in="query",
     *         required=false,
     *         description="URL signature for verification (included in email link)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="expires",
     *         in="query",
     *         required=false,
     *         description="Signature expiration timestamp",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File downloaded successfully",
     *         @OA\MediaType(mediaType="application/octet-stream")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid or expired signature (when using signed URL)"
     *     )
     * )
     */
    public function downloadExport($filename)
    {
        // Validate filename to prevent directory traversal, download .csv or .xlsx files only
        if (!preg_match('/^[a-zA-Z0-9_\-]+\.(xlsx|csv)$/', $filename)) {
            return $this->error('Invalid filename', 400);
        }

        $filePath = 'exports/' . $filename;

        // Check if file exists
        if (!Storage::disk('public')->exists($filePath)) {
            return $this->error('File not found', Response::HTTP_NOT_FOUND);
        }

        // Return file download
        $fullPath = storage_path('app/public/' . $filePath);
        return response()->download($fullPath, $filename);
    }
}
