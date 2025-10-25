<?php

use CleaniqueCoders\LaravelApiVersion\Http\Middleware\ApiVersion;
use CleaniqueCoders\LaravelApiVersion\Processors\DeprecationProcessor;
use Illuminate\Support\Facades\Route;

describe('Version Deprecation', function () {
    it('identifies deprecated versions correctly', function () {
        config(['api-version.deprecated_versions' => [
            'v1' => [
                'sunset_date' => '2024-12-31',
                'replacement' => 'v2',
                'message' => 'API v1 is deprecated.',
            ],
        ]]);

        expect(DeprecationProcessor::isDeprecated('v1'))->toBeTrue()
            ->and(DeprecationProcessor::isDeprecated('v2'))->toBeFalse();
    });

    it('returns correct deprecation information', function () {
        config(['api-version.deprecated_versions' => [
            'v1' => [
                'sunset_date' => '2024-12-31',
                'replacement' => 'v2',
                'message' => 'API v1 is deprecated.',
            ],
        ]]);

        $info = DeprecationProcessor::getDeprecationInfo('v1');

        expect($info)->toBe([
            'sunset_date' => '2024-12-31',
            'replacement' => 'v2',
            'message' => 'API v1 is deprecated.',
        ]);
    });

    it('generates correct deprecation headers', function () {
        config(['api-version.deprecated_versions' => [
            'v1' => [
                'sunset_date' => '2024-12-31',
                'replacement' => 'v2',
                'message' => 'API v1 is deprecated.',
            ],
        ]]);

        $headers = DeprecationProcessor::getDeprecationHeaders('v1');

        expect($headers)->toHaveKey('Deprecation', 'true')
            ->and($headers)->toHaveKey('Sunset', '2024-12-31')
            ->and($headers)->toHaveKey('X-API-Deprecation-Message', 'API v1 is deprecated.')
            ->and($headers)->toHaveKey('Link')
            ->and($headers['Link'])->toContain('v2');
    });

    it('returns empty headers for non-deprecated versions', function () {
        config(['api-version.deprecated_versions' => []]);

        $headers = DeprecationProcessor::getDeprecationHeaders('v1');

        expect($headers)->toBe([]);
    });

    it('adds deprecation headers to response when using deprecated version', function () {
        config(['api-version.deprecated_versions' => [
            'v1' => [
                'sunset_date' => '2024-12-31',
                'replacement' => 'v2',
                'message' => 'API v1 is deprecated.',
            ],
        ]]);

        Route::middleware(['api', ApiVersion::class])->get('/deprecated-test', function () {
            return response()->json(['message' => 'test']);
        });

        $response = $this->withHeader('X-API-Version', '1')
            ->get('/deprecated-test');

        $response->assertHeader('Deprecation', 'true')
            ->assertHeader('Sunset', '2024-12-31')
            ->assertHeader('X-API-Deprecation-Message', 'API v1 is deprecated.')
            ->assertHeader('Link');
    });

    it('validates deprecation configuration correctly', function () {
        // Valid configuration should not throw
        expect(function () {
            DeprecationProcessor::validateDeprecationConfig([
                'v1' => [
                    'sunset_date' => '2024-12-31',
                    'replacement' => 'v2',
                    'message' => 'API v1 is deprecated.',
                ],
            ]);
        })->not->toThrow(InvalidArgumentException::class);

        // Invalid version format should throw
        expect(fn () => DeprecationProcessor::validateDeprecationConfig([
            'invalid' => ['message' => 'test'],
        ]))->toThrow(InvalidArgumentException::class, 'Invalid deprecated version format');

        // Invalid sunset_date format should throw
        expect(fn () => DeprecationProcessor::validateDeprecationConfig([
            'v1' => ['sunset_date' => 'invalid-date'],
        ]))->toThrow(InvalidArgumentException::class, 'Invalid sunset_date format');
    });
});
