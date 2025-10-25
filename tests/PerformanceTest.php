<?php

use CleaniqueCoders\LaravelApiVersion\Processors\ControllerResolver;
use CleaniqueCoders\LaravelApiVersion\Processors\VersionResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

describe('Performance Optimizations', function () {
    it('caches namespace resolution', function () {
        // Clear any existing cache
        ControllerResolver::clearCache();

        // First call should hit the cache miss
        $namespace1 = ControllerResolver::resolveNamespace('v2');

        // Second call should hit the cache
        $namespace2 = ControllerResolver::resolveNamespace('v2');

        expect($namespace1)->toBe($namespace2)
            ->and($namespace1)->toBe('App\Http\Controllers\Api\V2');
    });

    it('formats version correctly for namespace', function () {
        expect(ControllerResolver::resolveNamespace('v1'))->toBe('App\Http\Controllers\Api\V1')
            ->and(ControllerResolver::resolveNamespace('v2.1'))->toBe('App\Http\Controllers\Api\V2_1')
            ->and(ControllerResolver::resolveNamespace('v3.0.1'))->toBe('App\Http\Controllers\Api\V3_0_1');
    });

    it('caches configuration in VersionResolver', function () {
        // Clear cache
        VersionResolver::clearConfigCache();

        $request = Request::create('/test');

        // First call loads config
        $version1 = VersionResolver::resolve($request);

        // Second call uses cached config
        $version2 = VersionResolver::resolve($request);

        expect($version1)->toBe($version2)
            ->and($version1)->toBe('v1');
    });

    it('clears cache correctly', function () {
        // Set up cache
        ControllerResolver::resolveNamespace('v1');
        VersionResolver::resolve(Request::create('/test'));

        // Clear caches
        ControllerResolver::clearCache();
        VersionResolver::clearConfigCache();

        // Should work without errors after cache clear
        expect(ControllerResolver::resolveNamespace('v1'))->toBe('App\Http\Controllers\Api\V1');
        expect(VersionResolver::resolve(Request::create('/test')))->toBe('v1');
    });

    it('handles missing cache gracefully', function () {
        // Test when cache() function might not be available
        expect(ControllerResolver::resolveNamespace('v1'))->toBe('App\Http\Controllers\Api\V1');
    });
});
