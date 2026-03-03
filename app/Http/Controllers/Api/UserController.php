<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\UserRequest;
use App\Contracts\UserServiceInterface;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends BaseController
{
    private UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="List all users",
     *     description="Get paginated list of users with optional search (Admin only)",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="search", in="query", description="Search by name or email", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", @OA\Schema(type="integer", example=10)),
     *     @OA\Response(
     *         response=200,
     *         description="User list retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required")
     * )
     */
    public function index(Request $request)
    {
        $data = $this->userService->getListData($request);
        return $this->success($data, 'User list retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create new user",
     *     description="Create a new user (Admin only). Validation: name required max:255, email required unique, password required min:6 confirmed, role required in:admin,user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data per UserRequest validation",
     *         @OA\JsonContent(
     *             required={"name","email","password","role"},
     *             @OA\Property(property="name", type="string", example="John Doe", maxLength=255),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", minLength=6),
     *             @OA\Property(property="role", type="string", enum={"admin","user"}, example="user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $user = $this->userService->createUser($data);

        return $this->success(new UserResource($user), 'User created successfully', Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get user detail",
     *     description="Get a single user's information (Admin only)",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="User ID", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function show($id)
    {
        $user = $this->userService->getUser($id);
        return $this->success(new UserResource($user), 'User retrieved successfully');
    }

    /**
     * @OA\Patch(
     *     path="/api/users/{id}",
     *     summary="Update user",
     *     description="Update user information (Admin only). Validation: email unique except current, password optional min:6",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="User ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data per UserRequest validation",
     *         @OA\JsonContent(
     *             required={"name","email","role"},
     *             @OA\Property(property="name", type="string", example="Updated Name", maxLength=255),
     *             @OA\Property(property="email", type="string", format="email", example="updated@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123", minLength=6, description="Optional, only provided if changing password"),
     *             @OA\Property(property="role", type="string", enum={"admin","user"}, example="admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required"),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UserRequest $request, $id)
    {
        $data = $request->validated();
        $user = $this->userService->updateUser($id, $data);
        return $this->success(new UserResource($user), 'User updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Soft delete user",
     *     description="Soft delete a user (Admin only)",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="User ID", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function destroy($id)
    {
        $this->userService->deleteUser($id);
        return $this->success(null, 'User deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/users/trashed",
     *     summary="Get soft deleted users",
     *     description="Get soft deleted users (Admin only)",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Trashed users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required")
     * )
     */
    public function trashed(Request $request)
    {
        $data = $this->userService->getTrashed($request);
        return $this->success($data, 'Trashed users retrieved successfully');
    }

    /**
     * @OA\Patch(
     *     path="/api/users/{id}/restore",
     *     summary="Restore soft deleted user",
     *     description="Restore a soft deleted user (Admin only)",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="User ID", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="User restored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Admin access required"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function restore($id)
    {
        $user = $this->userService->restoreUser($id);
        return $this->success(new UserResource($user), 'User restored successfully');
    }

    /**
    * @OA\Delete(
    *     path="/api/users/{id}/force-delete",
    *     summary="Permanently delete user",
    *     description="Permanently delete a user from database (Admin only)",
    *     tags={"Users"},
    *     security={{"bearerAuth":{}}},
    *     @OA\Parameter(name="id", in="path", required=true, description="User ID", @OA\Schema(type="integer")),
    *     @OA\Response(
    *         response=200,
    *         description="User permanently deleted",
    *         @OA\JsonContent(
    *             @OA\Property(property="success", type="boolean", example=true),
    *             @OA\Property(property="message", type="string")
    *         )
    *     ),
    *     @OA\Response(response=401, description="Unauthenticated"),
    *     @OA\Response(response=403, description="Admin access required"),
    *     @OA\Response(response=404, description="User not found")
    * )
    */
    public function forceDelete($id)
    {
        $this->userService->forceDeleteUser($id);
        return $this->success(null, 'User permanently deleted');
    }
}

