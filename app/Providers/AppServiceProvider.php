<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\UserServiceInterface;
use App\Contracts\AuthServiceInterface;
use App\Contracts\ProductServiceInterface;
use App\Contracts\CategoryServiceInterface;
use App\Contracts\ProfileServiceInterface;
use App\Contracts\FileUploadServiceInterface;
use App\Contracts\OrderServiceInterface;
use App\Services\UserService;
use App\Services\AuthService;
use App\Services\ProductService;
use App\Services\CategoryService;
use App\Services\ProfileService;
use App\Services\FileUploadService;
use App\Services\OrderService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Bind Service Interfaces to Implementations
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
        $this->app->bind(CategoryServiceInterface::class, CategoryService::class);
        $this->app->bind(ProfileServiceInterface::class, ProfileService::class);
        $this->app->bind(FileUploadServiceInterface::class, FileUploadService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
