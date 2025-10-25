<?php

namespace CleaniqueCoders\LaravelApiVersion\Processors;

use CleaniqueCoders\LaravelApiVersion\Exceptions\InvalidApiVersionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class VersionResolver
{
    /**
     * Cached configuration values to avoid repeated config() calls.
     */
    protected static ?array $cachedConfig = null;

    /**
     * Resolve the API version based on headers and config.
     */
    public static function resolve(Request $request, ?string $explicitVersion = null): string
    {
        // If explicit version is provided, validate and return it
        if ($explicitVersion !== null) {
            return self::validateVersion($explicitVersion);
        }

        $config = self::getConfig();

        $version = $config['default_version'];

        // Try custom header first (more explicit than Accept header)
        if ($request->hasHeader($config['custom_header'])) {
            $headerVersion = $request->header($config['custom_header'], '');
            if ($headerVersion) {
                $version = self::normalizeVersion($headerVersion);

                return self::validateVersion($version);
            }
        }

        // Fallback to Accept header if enabled
        if ($config['use_accept_header'] && $request->hasHeader('Accept')) {
            $acceptHeader = $request->header('Accept', '');
            if (is_string($acceptHeader) && preg_match($config['accept_header_pattern'], $acceptHeader, $matches)) {
                $version = 'v'.($matches[1] ?? '');

                return self::validateVersion($version);
            }
        }

        return self::validateVersion($version);
    }

    /**
     * Get cached configuration or load it.
     */
    protected static function getConfig(): array
    {
        if (self::$cachedConfig === null) {
            self::$cachedConfig = [
                'default_version' => Config::get('api-version.default_version', 'v1'),
                'use_accept_header' => Config::get('api-version.use_accept_header', true),
                'custom_header' => Config::get('api-version.custom_header', 'X-API-Version'),
                'accept_header_pattern' => Config::get('api-version.accept_header_pattern', '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/'),
                'supported_versions' => Config::get('api-version.supported_versions', []),
            ];

            // Ensure all values are properly typed
            self::$cachedConfig['default_version'] = is_string(self::$cachedConfig['default_version']) ? self::$cachedConfig['default_version'] : 'v1';
            self::$cachedConfig['custom_header'] = is_string(self::$cachedConfig['custom_header']) ? self::$cachedConfig['custom_header'] : 'X-API-Version';
            self::$cachedConfig['accept_header_pattern'] = is_string(self::$cachedConfig['accept_header_pattern']) ? self::$cachedConfig['accept_header_pattern'] : '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/';
        }

        return self::$cachedConfig;
    }

    /**
     * Clear cached configuration (useful for testing).
     */
    public static function clearConfigCache(): void
    {
        self::$cachedConfig = null;
    }

    /**
     * Validate the API version format and check if it's supported.
     */
    public static function validateVersion(string $version): string
    {
        // Normalize the version (ensure it starts with 'v')
        $normalizedVersion = self::normalizeVersion($version);

        // Check format: must be v followed by digits and optional dots
        if (! preg_match('/^v\d+(\.\d+)*$/', $normalizedVersion)) {
            throw InvalidApiVersionException::invalidFormat($version);
        }

        // Check if version is in supported versions list (if configured)
        $supportedVersions = Config::get('api-version.supported_versions', []);
        if (! empty($supportedVersions) && ! in_array($normalizedVersion, $supportedVersions)) {
            throw InvalidApiVersionException::unsupportedVersion($normalizedVersion, $supportedVersions);
        }

        return $normalizedVersion;
    }

    /**
     * Normalize version string to ensure consistent format.
     */
    public static function normalizeVersion(string $version): string
    {
        $version = trim($version);

        // Add 'v' prefix if not present
        if (! str_starts_with($version, 'v')) {
            $version = 'v'.$version;
        }

        return $version;
    }

    /**
     * Determine if the request qualifies as an API request.
     */
    public static function isApiRequest(Request $request): bool
    {
        $config = self::getConfig();

        // Check Accept header pattern for API version if enabled
        if ($config['use_accept_header'] && $request->hasHeader('Accept')) {
            $acceptHeader = $request->header('Accept', '');
            if (is_string($acceptHeader) && preg_match($config['accept_header_pattern'], $acceptHeader)) {
                return true;
            }
        }

        // Check for the presence of the custom API version header
        return $request->hasHeader($config['custom_header']);
    }
}
