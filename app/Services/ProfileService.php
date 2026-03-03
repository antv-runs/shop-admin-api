<?php

namespace App\Services;

use App\Contracts\ProfileServiceInterface;
use Illuminate\Support\Facades\Storage;

class ProfileService implements ProfileServiceInterface
{
    /**
     * Update user profile information, managing image if present.
     *
     * @return \App\Models\User
     */
    public function updateProfile($user, array $data)
    {
        // Handle image upload
        if (isset($data['profile_image'])) {
            // Delete old image if exists
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Store new image
            $path = $data['profile_image']->store('profile-images', 'public');
            $data['profile_image'] = $path;
        }

        $user->update($data);

        return $user;
    }

    /**
     * Delete user's profile image and return updated user.
     */
    public function deleteProfileImage($user)
    {
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->update(['profile_image' => null]);

        return $user;
    }
}
