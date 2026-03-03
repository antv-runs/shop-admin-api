<?php

namespace App\Http\Controllers\Api;

use App\Contracts\ProfileServiceInterface;
use App\Http\Requests\ProfileRequest;
use App\Http\Resources\UserResource;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends BaseController
{
    private ProfileServiceInterface $profileService;

    public function __construct(ProfileServiceInterface $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Get authenticated user's profile
     *
     * @OA\Get(
     *     path="/api/profile",
     *     summary="Get user profile",
     *     description="Get the authenticated user's profile information",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profile retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Edit profile"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function edit()
    {
        $user = auth()->user();
        return $this->success(new UserResource($user), 'Edit profile');
    }

    /**
     * Update authenticated user's profile
     *
     * @OA\Patch(
     *     path="/api/profile",
     *     summary="Update user profile",
     *     description="Update the authenticated user's profile information including bio and profile image",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Profile update data",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="bio", type="string", description="User bio", example="I am a developer"),
     *                 @OA\Property(
     *                     property="profile_image",
     *                     type="string",
     *                     format="binary",
     *                     description="Profile image file (jpg, png, gif)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(ProfileRequest $request)
    {
        $user = auth()->user();
        $validated = $request->validated();

        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image');
        }

        $user = $this->profileService->updateProfile($user, $validated);

        return $this->success(new UserResource($user), 'Profile updated successfully');
    }

    /**
     * Delete authenticated user's profile image
     *
     * @OA\Delete(
     *     path="/api/profile/image",
     *     summary="Delete profile image",
     *     description="Delete the authenticated user's profile image",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profile image deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile image deleted successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function deleteImage()
    {
        $user = auth()->user();
        $user = $this->profileService->deleteProfileImage($user);

        return $this->success(new UserResource($user), 'Profile image deleted successfully');
    }
}
