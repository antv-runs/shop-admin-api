<?php

namespace App\Contracts;

use App\Models\User;

use App\Exceptions\BusinessException;

interface ProfileServiceInterface
{
    /**
     * Update user profile
     *
     * @return User
     * @throws BusinessException
     */
    public function updateProfile(User $user, array $data);

    /**
     * Delete user's profile image
     *
     * @return User
     */
    public function deleteProfileImage($user);
}
