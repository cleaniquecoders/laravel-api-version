<?php

use CleaniqueCoders\LaravelApiVersion\LaravelApiVersionServiceProvider;

describe('Configuration Validation', function () {
    beforeEach(function () {
        $this->provider = new LaravelApiVersionServiceProvider($this->app);
    });

    it('validates default_version format', function () {
        config(['api-version.default_version' => 'invalid']);

        expect(fn () => $this->provider->bootingPackage())
            ->toThrow(InvalidArgumentException::class, 'Invalid \'default_version\' in api-version config');
    });

    it('validates custom_header is not empty', function () {
        config(['api-version.custom_header' => '']);

        expect(fn () => $this->provider->bootingPackage())
            ->toThrow(InvalidArgumentException::class, 'Invalid \'custom_header\' in api-version config');
    });

    it('validates accept_header_pattern is valid regex', function () {
        config(['api-version.accept_header_pattern' => '[invalid regex']);

        expect(fn () => $this->provider->bootingPackage())
            ->toThrow(InvalidArgumentException::class, 'Invalid regex pattern');
    });

    it('validates root_namespace is not empty', function () {
        config(['api-version.root_namespace' => '']);

        expect(fn () => $this->provider->bootingPackage())
            ->toThrow(InvalidArgumentException::class, 'Invalid \'root_namespace\' in api-version config');
    });

    it('validates supported_versions is an array', function () {
        config(['api-version.supported_versions' => 'not-an-array']);

        expect(fn () => $this->provider->bootingPackage())
            ->toThrow(InvalidArgumentException::class, 'Invalid \'supported_versions\' in api-version config');
    });

    it('validates each version in supported_versions has correct format', function () {
        config(['api-version.supported_versions' => ['v1', 'invalid', 'v2']]);

        expect(fn () => $this->provider->bootingPackage())
            ->toThrow(InvalidArgumentException::class, 'Invalid version format in \'supported_versions\'');
    });

    it('validates default_version exists in supported_versions when provided', function () {
        config([
            'api-version.default_version' => 'v1',
            'api-version.supported_versions' => ['v2', 'v3'],
        ]);

        expect(fn () => $this->provider->bootingPackage())
            ->toThrow(InvalidArgumentException::class, 'The \'default_version\' (v1) must be included in \'supported_versions\'');
    });

    it('passes validation with correct configuration', function () {
        config([
            'api-version.default_version' => 'v1',
            'api-version.custom_header' => 'X-API-Version',
            'api-version.accept_header_pattern' => '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/',
            'api-version.root_namespace' => 'App\\Http\\Controllers\\Api',
            'api-version.supported_versions' => ['v1', 'v2', 'v3'],
        ]);

        expect(function () {
            $this->provider->bootingPackage();
        })->not->toThrow(InvalidArgumentException::class);
    });
});
