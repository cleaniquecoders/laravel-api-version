<?php

namespace CleaniqueCoders\LaravelApiVersion;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use CleaniqueCoders\LaravelApiVersion\Commands\LaravelApiVersionCommand;

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
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_api_version_table')
            ->hasCommand(LaravelApiVersionCommand::class);
    }
}
