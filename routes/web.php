<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
| NOTE: This API is running in API-only mode. All endpoints return JSON.
|       Use the /api routes for all business logic.
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'message' => 'Server is running']);
});

// API Documentation/Info endpoint
Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'version' => '1.0',
        'message' => 'Welcome to Shop Admin API',
        'api_routes' => [
            'auth' => '/api/auth',
            'users' => '/api/users',
            'products' => '/api/products',
            'categories' => '/api/categories',
            'profile' => '/api/profile'
        ],
        'documentation' => 'See API documentation for more details'
    ]);
});
