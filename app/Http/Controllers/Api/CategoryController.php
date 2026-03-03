<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Contracts\CategoryServiceInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends BaseController
{
    private CategoryServiceInterface $categoryService;

    public function __construct(CategoryServiceInterface $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Get all categories with pagination and search
     *
     * @OA\Get(
     *     path="/api/categories",
     *     summary="List all categories",
     *     description="Get paginated list of categories with optional search",
     *     tags={"Categories"},
     *     @OA\Parameter(name="search", in="query", description="Search by category name", @OA\Schema(type="string")),
     *     @OA\Parameter(name="page", in="query", description="Page number", @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Category"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $categories = $this->categoryService->getAllCategories($request, $perPage);
        return $this->success(
            CategoryResource::collection($categories),
            'Categories retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Create new category",
     *     description="Create a new category (Admin only). Validation: name required min:3 max:100 unique, description nullable string",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Category data per CategoryRequest validation",
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Electronics", minLength=3, maxLength=100),
     *             @OA\Property(property="description", type="string", example="All electronic devices")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Category")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required"),
     *     @OA\Response(response=422, description="Validation error (name already exists or invalid format)")
     * )
     */
    public function store(CategoryRequest $request)
    {
        $data = $request->only(['name', 'description']);
        $category = $this->categoryService->createCategory($data);

        return $this->success(
            new CategoryResource($category),
            'Category created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Get a specific category detail by ID
     *
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     summary="Get category detail",
     *     description="Retrieve a single category by ID",
     *     tags={"Categories"},
     *     @OA\Parameter(name="id", in="path", required=true, description="Category ID", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Category")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function show($id)
    {
        $category = $this->categoryService->getCategory($id);
        return $this->success(
            new CategoryResource($category),
            'Category retrieved successfully'
        );
    }

    /**
     * @OA\Patch(
     *     path="/api/categories/{id}",
     *     summary="Update category",
     *     description="Update a category (Admin only). Validation: name required min:3 max:100 unique (except current), description nullable string",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Category ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Category data per CategoryRequest validation",
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", minLength=3, maxLength=100),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Category")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required"),
     *     @OA\Response(response=404, description="Category not found"),
     *     @OA\Response(response=422, description="Validation error (name already exists)")
     * )
     */
    public function update(CategoryRequest $request, $id)
    {
        $category = $this->categoryService->getCategory($id);
        $data = $request->only(['name', 'description']);
        $result = $this->categoryService->updateCategory($category, $data);

        return $this->success(
            new CategoryResource($result),
            'Category updated successfully'
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     summary="Soft delete category",
     *     description="Soft delete a category (Admin only)",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Category ID", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required"),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function destroy($id)
    {
        $this->categoryService->deleteCategory($id);
        return $this->success(
            null,
            'Category deleted successfully'
        );
    }

    /**
     * Get soft deleted categories
     *
     * @OA\Get(
     *     path="/api/categories/trashed",
     *     summary="Get soft deleted categories",
     *     description="Get soft deleted categories with pagination (Admin only)",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", description="Page number", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", @OA\Schema(type="integer", example=10)),
     *     @OA\Response(
     *         response=200,
     *         description="Trashed categories retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Category"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required")
     * )
     */
    public function trashed(Request $request)
    {
        $categories = $this->categoryService->getTrashed(15);
        return $this->success(
            CategoryResource::collection($categories),
            'Trashed categories retrieved successfully'
        );
    }

    /**
     * @OA\Patch(
     *     path="/api/categories/{id}/restore",
     *     summary="Restore soft deleted category",
     *     description="Restore a soft deleted category (Admin only)",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Category ID", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Category restored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Category")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required"),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function restore($id)
    {
        $category = $this->categoryService->restoreCategory($id);
        return $this->success(
            new CategoryResource($category),
            'Category restored successfully'
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{id}/force-delete",
     *     summary="Permanently delete category",
     *     description="Permanently delete a category (cannot be restored, Admin only)",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Category ID", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Category permanently deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required"),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function forceDelete($id)
    {
        $this->categoryService->forceDeleteCategory($id);

        return $this->success(
            null,
            'Category permanently deleted'
        );
    }
}
