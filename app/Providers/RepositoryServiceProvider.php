<?php

namespace App\Providers;

use App\Http\Repositories\AttendanceRepository;
use App\Http\Repositories\Implements\AttendanceRepositoryImpl;
use Illuminate\Support\ServiceProvider;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\Implements\UserRepositoryImpl;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepository::class, UserRepositoryImpl::class);
        $this->app->bind(AttendanceRepository::class, AttendanceRepositoryImpl::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
