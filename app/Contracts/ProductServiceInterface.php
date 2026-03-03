<?php

namespace App\Contracts;

use App\Models\Product;

interface ProductServiceInterface
{
    /**
     * Get all products with category
     */
    public function getAllProducts(\Illuminate\Http\Request $request, $perPage = 10);

    /**
     * Get all categories
     */
    public function getCategories();

    /**
     * Create a new product
     */
    public function createProduct(array $data);

    /**
     * Get product by ID
     */
    public function getProduct($id);

    /**
     * Update product
     */
    public function updateProduct(Product $product, array $data);

    /**
     * Delete product (soft delete)
     */
    public function deleteProduct($id);

    /**
     * Get trashed products
     */
    public function getTrashed($perPage = 10);

    /**
     * Restore product
     */
    public function restoreProduct($id);

    /**
     * Force delete product
     */
    public function forceDeleteProduct($id);

    /**
     * Export products to CSV/Excel via queue
     */
    public function exportProducts(int $userId, array $filters = [], string $format = 'csv');
}
