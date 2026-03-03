<?php

namespace App\Enums;

/**
 * User role enumeration
 *
 * PHP 8.1 enum for type-safe user role handling
 */
enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    /**
     * Get readable label for the role
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Admin',
            self::USER => 'User',
        };
    }

    /**
     * Check if role is admin
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Get all available roles as array for select dropdowns
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $role) => [
            $role->value => $role->label()
        ])->toArray();
    }
}
