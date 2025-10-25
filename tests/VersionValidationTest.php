<?php

use CleaniqueCoders\LaravelApiVersion\Exceptions\InvalidApiVersionException;
use CleaniqueCoders\LaravelApiVersion\Processors\VersionResolver;
use Illuminate\Http\Request;

describe('VersionResolver Validation', function () {
    it('validates correct version formats', function () {
        $request = Request::create('/test');

        expect(VersionResolver::validateVersion('v1'))->toBe('v1')
            ->and(VersionResolver::validateVersion('v2.1'))->toBe('v2.1')
            ->and(VersionResolver::validateVersion('v1.0.1'))->toBe('v1.0.1');
    });

    it('normalizes version strings by adding v prefix', function () {
        expect(VersionResolver::normalizeVersion('1'))->toBe('v1')
            ->and(VersionResolver::normalizeVersion('2.1'))->toBe('v2.1')
            ->and(VersionResolver::normalizeVersion('v1'))->toBe('v1');
    });

    it('throws exception for invalid version formats', function () {
        expect(fn () => VersionResolver::validateVersion('invalid'))
            ->toThrow(InvalidApiVersionException::class, 'Invalid API version format');

        expect(fn () => VersionResolver::validateVersion('v'))
            ->toThrow(InvalidApiVersionException::class, 'Invalid API version format');

        expect(fn () => VersionResolver::validateVersion('1.a'))
            ->toThrow(InvalidApiVersionException::class, 'Invalid API version format');
    });

    it('validates against supported versions when configured', function () {
        config(['api-version.supported_versions' => ['v1', 'v2', 'v3']]);

        expect(VersionResolver::validateVersion('v1'))->toBe('v1')
            ->and(VersionResolver::validateVersion('v2'))->toBe('v2');

        expect(fn () => VersionResolver::validateVersion('v4'))
            ->toThrow(InvalidApiVersionException::class, 'Unsupported API version');
    });

    it('resolves explicit version parameter correctly', function () {
        $request = Request::create('/test');

        expect(VersionResolver::resolve($request, 'v2'))->toBe('v2')
            ->and(VersionResolver::resolve($request, '3'))->toBe('v3');
    });

    it('prioritizes custom header over accept header', function () {
        $request = Request::create('/test');
        $request->headers->set('X-API-Version', '2');
        $request->headers->set('Accept', 'application/vnd.app+v1+json');

        expect(VersionResolver::resolve($request))->toBe('v2');
    });

    it('throws exception for invalid explicit version', function () {
        $request = Request::create('/test');

        expect(fn () => VersionResolver::resolve($request, 'invalid'))
            ->toThrow(InvalidApiVersionException::class);
    });
});
