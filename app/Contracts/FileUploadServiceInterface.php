<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

/**
 * File Upload Service Contract
 *
 * Dependency Inversion Principle: depend on abstraction, not concrete class
 * Allows easy implementation swapping (local storage, cloud storage, etc)
 */
interface FileUploadServiceInterface
{
    /**
     * Upload a product image and return the path
     */
    public function uploadProductImage(UploadedFile $file): string;

    /**
     * Delete a file from storage
     */
    public function deleteFile(string $path): void;

    /**
     * Replace old file with new one
     */
    public function replaceFile(?string $oldPath, UploadedFile $newFile): string;
}
