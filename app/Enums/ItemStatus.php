<?php

namespace App\Enums;

/**
 * Item status enumeration for listing filters
 *
 * Used for filtering products, categories, users by soft delete state
 */
enum ItemStatus: string
{
    case ACTIVE = 'active';
    case DELETED = 'deleted';
    case ALL = 'all';

    /**
     * Get readable label for the status
     */
    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::DELETED => 'Deleted',
            self::ALL => 'All',
        };
    }

    /**
     * Check if status is active
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Check if status is deleted
     */
    public function isDeleted(): bool
    {
        return $this === self::DELETED;
    }

    /**
     * Get all available statuses as array for select dropdowns
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $status) => [
            $status->value => $status->label()
        ])->toArray();
    }
}
