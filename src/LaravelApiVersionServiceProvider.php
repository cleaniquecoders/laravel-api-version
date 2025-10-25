<?php

namespace CleaniqueCoders\LaravelApiVersion;

use CleaniqueCoders\LaravelApiVersion\Exceptions\ApiExceptionHandler;
use CleaniqueCoders\LaravelApiVersion\Http\Middleware\ApiVersion;
use CleaniqueCoders\LaravelApiVersion\Processors\DeprecationProcessor;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Routing\Router;
use InvalidArgumentException;
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
        // Validate configuration
        $this->validateConfiguration();

        // Explicitly resolve the router instance as a Router class
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('api.version', ApiVersion::class);

        $this->app->singleton(ExceptionHandler::class, ApiExceptionHandler::class);
    }

    /**
     * Validate the package configuration.
     */
    protected function validateConfiguration(): void
    {
        $config = config('api-version', []);

        // Validate default_version
        $defaultVersion = $config['default_version'] ?? 'v1';
        if (! is_string($defaultVersion) || ! preg_match('/^v\d+(\.\d+)*$/', $defaultVersion)) {
            throw new InvalidArgumentException(
                "Invalid 'default_version' in api-version config. Expected format: v1, v2, v1.1, etc. Got: {$defaultVersion}"
            );
        }

        // Validate custom_header
        $customHeader = $config['custom_header'] ?? 'X-API-Version';
        if (! is_string($customHeader) || trim($customHeader) === '') {
            throw new InvalidArgumentException(
                "Invalid 'custom_header' in api-version config. Must be a non-empty string. Got: ".gettype($customHeader)
            );
        }

        // Validate accept_header_pattern
        $pattern = $config['accept_header_pattern'] ?? '';
        if (! is_string($pattern) || trim($pattern) === '') {
            throw new InvalidArgumentException(
                "Invalid 'accept_header_pattern' in api-version config. Must be a non-empty string regex pattern."
            );
        }

        // Test the regex pattern
        if (@preg_match($pattern, '') === false) {
            throw new InvalidArgumentException(
                "Invalid regex pattern in 'accept_header_pattern': {$pattern}"
            );
        }

        // Validate root_namespace
        $rootNamespace = $config['root_namespace'] ?? 'App\\Http\\Controllers\\Api';
        if (! is_string($rootNamespace) || trim($rootNamespace) === '') {
            throw new InvalidArgumentException(
                "Invalid 'root_namespace' in api-version config. Must be a non-empty string. Got: ".gettype($rootNamespace)
            );
        }

        // Validate supported_versions if provided
        $supportedVersions = $config['supported_versions'] ?? [];
        if (! is_array($supportedVersions)) {
            throw new InvalidArgumentException(
                "Invalid 'supported_versions' in api-version config. Must be an array. Got: ".gettype($supportedVersions)
            );
        }

        foreach ($supportedVersions as $version) {
            if (! is_string($version) || ! preg_match('/^v\d+(\.\d+)*$/', $version)) {
                throw new InvalidArgumentException(
                    "Invalid version format in 'supported_versions': {$version}. Expected format: v1, v2, v1.1, etc."
                );
            }
        }

        // Validate that default_version is in supported_versions if supported_versions is not empty
        if (! empty($supportedVersions) && ! in_array($defaultVersion, $supportedVersions)) {
            throw new InvalidArgumentException(
                "The 'default_version' ({$defaultVersion}) must be included in 'supported_versions' when supported_versions is specified."
            );
        }

        // Validate deprecated_versions configuration
        $deprecatedVersions = $config['deprecated_versions'] ?? [];
        if (! is_array($deprecatedVersions)) {
            throw new InvalidArgumentException(
                "Invalid 'deprecated_versions' in api-version config. Must be an array. Got: ".gettype($deprecatedVersions)
            );
        }

        DeprecationProcessor::validateDeprecationConfig($deprecatedVersions);
    }
}
