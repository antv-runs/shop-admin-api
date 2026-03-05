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
*/

// ==========================
// Auth (Public)
// ==========================
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});


// ==========================
// Public resources
// ==========================

// Products
Route::prefix('products')->controller(ProductController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/exports/{filename}', 'downloadExport')->name('products.download-export');
    Route::get('/{id}', 'show')->where('id', '[0-9]+');
});

// Categories
Route::prefix('categories')->controller(CategoryController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{id}', 'show')->where('id', '[0-9]+');
});


// ==========================
// Protected Routes
// ==========================
Route::middleware('auth:sanctum')->group(function () {

    // Auth actions
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout');
        Route::get('/me', 'me');
    });

    // Profile
    Route::prefix('profile')->controller(ProfileController::class)->group(function () {
        Route::patch('/', 'update');
        Route::delete('/image', 'deleteImage');
    });

    // Orders
    Route::prefix('orders')->controller(OrderController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::post('/', 'store');
    });

    // ==========================
    // Admin routes
    // ==========================
    Route::middleware('is_admin')->group(function () {

        // Users
        Route::prefix('users')->controller(UserController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/trashed', 'trashed');

            Route::get('/{id}', 'show');
            Route::patch('/{id}', 'update');
            Route::delete('/{id}', 'destroy');

            Route::patch('/{id}/restore', 'restore');
            Route::delete('/{id}/force-delete', 'forceDelete');
        });

        // Products
        Route::prefix('products')->controller(ProductController::class)->group(function () {
            Route::post('/', 'store');
            Route::get('/trashed', 'trashed');
            Route::post('/export', 'export');

            Route::patch('/{id}', 'update')->where('id', '[0-9]+');
            Route::delete('/{id}', 'destroy')->where('id', '[0-9]+');

            Route::patch('/{id}/restore', 'restore')->where('id', '[0-9]+');
            Route::delete('/{id}/force-delete', 'forceDelete')->where('id', '[0-9]+');
        });

        // Categories
        Route::prefix('categories')->controller(CategoryController::class)->group(function () {
            Route::post('/', 'store');
            Route::get('/trashed', 'trashed');

            Route::patch('/{id}', 'update')->where('id', '[0-9]+');
            Route::delete('/{id}', 'destroy')->where('id', '[0-9]+');

            Route::patch('/{id}/restore', 'restore')->where('id', '[0-9]+');
            Route::delete('/{id}/force-delete', 'forceDelete')->where('id', '[0-9]+');
        });

    });

});
