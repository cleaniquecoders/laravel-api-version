<?php

namespace CleaniqueCoders\LaravelApiVersion;

use CleaniqueCoders\LaravelApiVersion\Exceptions\ApiExceptionHandler;
use CleaniqueCoders\LaravelApiVersion\Http\Middleware\ApiVersion;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Routing\Router;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelApiVersionServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-api-version')
            ->hasConfigFile();
    }

    public function bootingPackage(): void
    {
        // Explicitly resolve the router instance as a Router class
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('api.version', ApiVersion::class);

        $this->app->singleton(ExceptionHandler::class, ApiExceptionHandler::class);
    }
}
