<?php

namespace App\Providers;

use App\Repositories\API\V1\Auth\ForgetPasswordRepository;
use App\Repositories\API\V1\Auth\ForgetPasswordRepositoryInterface;
use App\Repositories\API\V1\Auth\OTPRepository;
use App\Repositories\API\V1\Auth\OTPRepositoryInterface;
use App\Repositories\API\V1\Auth\PasswordRepository;
use App\Repositories\API\V1\Auth\PasswordRepositoryInterface;
use App\Repositories\API\V1\Auth\UserRepository;
use App\Repositories\API\V1\Auth\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ForgetPasswordRepositoryInterface::class, ForgetPasswordRepository::class);
        $this->app->bind(OTPRepositoryInterface::class, OTPRepository::class);
        $this->app->bind(PasswordRepositoryInterface::class, PasswordRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
