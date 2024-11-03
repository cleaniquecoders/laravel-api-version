<?php

namespace CleaniqueCoders\LaravelApiVersion;

use CleaniqueCoders\LaravelApiVersion\Http\Middleware\ApiVersion;
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

    public function bootingPackage()
    {
        // Register the api.version middleware alias
        $this->app['router']->aliasMiddleware('api.version', ApiVersion::class);
    }
}
