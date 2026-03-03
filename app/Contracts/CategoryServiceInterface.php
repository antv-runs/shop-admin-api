<?php

namespace App\Contracts;

use App\Models\Category;

interface CategoryServiceInterface
{
    /**
     * Get all categories
     */
    public function getAllCategories(\Illuminate\Http\Request $request, $perPage = 15);

    /**
     * Get category by ID
     */
    public function getCategory($id);

    /**
     * Create a new category
     */
    public function createCategory(array $data);

    /**
     * Update category
     */
    public function updateCategory(Category $category, array $data);

    /**
     * Delete category (soft delete)
     */
    public function deleteCategory($id);

    /**
     * Get trashed categories
     */
    public function getTrashed($perPage = 15);

    /**
     * Restore category
     */
    public function restoreCategory($id);

    /**
     * Force delete category
     */
    public function forceDeleteCategory($id);
}
