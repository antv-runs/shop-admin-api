<?php

namespace App\Services;

use App\Contracts\CategoryServiceInterface;
use App\Models\Category;
use App\Enums\ItemStatus;
use Illuminate\Support\Str;

class CategoryService implements CategoryServiceInterface
{
    /**
     * Get all categories
     */
    public function getAllCategories(\Illuminate\Http\Request $request, $perPage = 15)
    {
        $perPage = (int)$request->input('per_page', $perPage);

        $status = $request->input('status', ItemStatus::ACTIVE->value);

        if ($status === ItemStatus::DELETED->value) {
            $query = Category::onlyTrashed();
        } elseif ($status === ItemStatus::ALL->value) {
            $query = Category::withTrashed();
        } else {
            $query = Category::query();
        }

        // Search by name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Sort
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        if (in_array($sortBy, ['id', 'name', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        return $query->paginate($perPage);
    }

    /**
     * Get category by ID
     */
    public function getCategory($id)
    {
        return Category::findOrFail($id);
    }

    /**
     * Generate unique slug
     */
    private function generateUniqueSlug($name, $excludeId = null)
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        $query = Category::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $original . '-' . $i++;
            $query = Category::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Create a new category
     */
    public function createCategory(array $data)
    {
        $data['slug'] = $this->generateUniqueSlug($data['name']);
        return Category::create($data);
    }

    /**
     * Update category
     */
    public function updateCategory(Category $category, array $data)
    {
        $data['slug'] = $this->generateUniqueSlug($data['name'], $category->id);
        $category->update($data);
        return $category;
    }

    /**
     * Delete category (soft delete)
     */
    public function deleteCategory($id)
    {
        $category = $this->getCategory($id);
        $category->delete();
        return true;
    }

    /**
     * Get trashed categories
     */
    public function getTrashed($perPage = 15)
    {
        return Category::onlyTrashed()->latest('deleted_at')->paginate($perPage);
    }

    /**
     * Restore category
     */
    public function restoreCategory($id)
    {
        $category = Category::withTrashed()->findOrFail($id);

        if (! $category->trashed()) {
            throw new \Exception('Category is not deleted');
        }

        $category->restore();
        return $category;
    }

    /**
     * Force delete category
     */
    public function forceDeleteCategory($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->forceDelete();
    }
}
