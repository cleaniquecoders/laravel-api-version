<?php

namespace CleaniqueCoders\LaravelApiVersion\Processors;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class ControllerResolver
{
    /**
     * Resolve the controller namespace based on the API version.
     */
    public static function resolveNamespace(string $version): string
    {
        // Use caching for performance if cache is available
        if (function_exists('cache')) {
            return Cache::remember(
                "api_version_namespace_{$version}",
                3600, // 1 hour
                fn () => self::buildNamespace($version)
            );
        }

        return self::buildNamespace($version);
    }

    /**
     * Build the namespace without caching.
     */
    protected static function buildNamespace(string $version): string
    {
        $rootNamespace = Config::get('api-version.root_namespace', 'App\Http\Controllers\Api');

        // Ensure rootNamespace is a string, or fallback to default
        if (! is_string($rootNamespace)) {
            $rootNamespace = 'App\Http\Controllers\Api';
        }

        // Convert version to proper namespace format (v2.1 -> V2_1)
        $versionNamespace = self::formatVersionForNamespace($version);

        return rtrim($rootNamespace, '\\').'\\'.$versionNamespace;
    }

    /**
     * Format version for namespace usage.
     */
    protected static function formatVersionForNamespace(string $version): string
    {
        // Remove 'v' prefix and convert dots to underscores for valid PHP namespace
        $cleanVersion = ltrim($version, 'v');
        $formatted = str_replace('.', '_', $cleanVersion);

        // Capitalize first letter
        return ucfirst('V'.$formatted);
    }

    /**
     * Clear cached namespaces (useful for testing or config changes).
     */
    public static function clearCache(): void
    {
        if (function_exists('cache')) {
            // Clear all cached namespaces - in production this might be more targeted
            Cache::forget('api_version_namespace_*');
        }
    }
}
