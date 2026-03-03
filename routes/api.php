<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
| All routes return JSON responses.
|
*/

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Products and Categories (public read-only)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// Download export files (protected) - must be before /products/{id} to avoid conflict
Route::middleware('auth:sanctum')->get('/products/exports/{filename}', [ProductController::class, 'downloadExport']);

// Product detail (public read-only) - after exports to avoid conflict
Route::get('/products/{id}', [ProductController::class, 'show']);

// Protected routes (requires authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth endpoints
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // User info
    Route::get('/auth/me', [AuthController::class, 'me']);

    // User Profile routes
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile/image', [ProfileController::class, 'deleteImage']);

    // Order endpoints for authenticated users
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);

    // Admin only routes
    Route::middleware(['is_admin'])->group(function () {
        // Users Management
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::patch('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

        // Soft delete for users
        Route::get('/users/trashed', [UserController::class, 'trashed']);
        Route::patch('/users/{id}/restore', [UserController::class, 'restore']);
        Route::delete('/users/{id}/force-delete', [UserController::class, 'forceDelete']);

        // Products Management
        Route::post('/products', [ProductController::class, 'store']);
        Route::patch('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        Route::post('/products/export', [ProductController::class, 'export']);

        // Soft delete for products
        Route::get('/products/trashed', [ProductController::class, 'trashed']);
        Route::patch('/products/{id}/restore', [ProductController::class, 'restore']);
        Route::delete('/products/{id}/force-delete', [ProductController::class, 'forceDelete']);

        // Categories Management
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::patch('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        // Soft delete for categories
        Route::get('/categories/trashed', [CategoryController::class, 'trashed']);
        Route::patch('/categories/{id}/restore', [CategoryController::class, 'restore']);
        Route::delete('/categories/{id}/force-delete', [CategoryController::class, 'forceDelete']);
    });
});
