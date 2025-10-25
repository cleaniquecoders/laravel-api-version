<?php

namespace CleaniqueCoders\LaravelApiVersion\Processors;

use CleaniqueCoders\LaravelApiVersion\Exceptions\InvalidApiVersionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class VersionResolver
{
    /**
     * Cached configuration values to avoid repeated config() calls.
     *
     * @var array<string, mixed>|null
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
        $customHeader = $config['custom_header'];
        if (is_string($customHeader) && $customHeader !== '' && $request->hasHeader($customHeader)) {
            $headerVersion = $request->header($customHeader, '');
            if (is_string($headerVersion) && $headerVersion !== '') {
                $version = self::normalizeVersion($headerVersion);

                return self::validateVersion($version);
            }
        }

        // Fallback to Accept header if enabled
        $acceptHeaderPattern = $config['accept_header_pattern'];
        if ($config['use_accept_header'] && is_string($acceptHeaderPattern) && $acceptHeaderPattern !== '' && $request->hasHeader('Accept')) {
            $acceptHeader = $request->header('Accept', '');
            if (is_string($acceptHeader) && preg_match($acceptHeaderPattern, $acceptHeader, $matches)) {
                $version = 'v'.($matches[1] ?? '');

                return self::validateVersion($version);
            }
        }

        return self::validateVersion(is_string($version) ? $version : 'v1');
    }

    /**
     * Get cached configuration for version resolution.
     *
     * @return array<string, mixed>
     */
    protected static function getConfig(): array
    {
        if (self::$cachedConfig === null) {
            $rawConfig = [
                'default_version' => Config::get('api-version.default_version', 'v1'),
                'use_accept_header' => Config::get('api-version.use_accept_header', true),
                'custom_header' => Config::get('api-version.custom_header', 'X-API-Version'),
                'accept_header_pattern' => Config::get('api-version.accept_header_pattern', '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/'),
                'supported_versions' => Config::get('api-version.supported_versions', []),
            ];

            // Build properly typed config array
            $supportedVersions = $rawConfig['supported_versions'];
            $filteredVersions = [];
            if (is_array($supportedVersions)) {
                foreach ($supportedVersions as $version) {
                    if (is_string($version)) {
                        $filteredVersions[] = $version;
                    }
                }
            }

            self::$cachedConfig = [
                'default_version' => is_string($rawConfig['default_version']) ? $rawConfig['default_version'] : 'v1',
                'use_accept_header' => is_bool($rawConfig['use_accept_header']) ? $rawConfig['use_accept_header'] : true,
                'custom_header' => is_string($rawConfig['custom_header']) ? $rawConfig['custom_header'] : 'X-API-Version',
                'accept_header_pattern' => is_string($rawConfig['accept_header_pattern']) ? $rawConfig['accept_header_pattern'] : '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/',
                'supported_versions' => $filteredVersions,
            ];
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
        /** @var array<string> $supportedVersions */
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
        $acceptHeaderPattern = $config['accept_header_pattern'];
        if ($config['use_accept_header'] && is_string($acceptHeaderPattern) && $acceptHeaderPattern !== '' && $request->hasHeader('Accept')) {
            $acceptHeader = $request->header('Accept', '');
            if (is_string($acceptHeader) && preg_match($acceptHeaderPattern, $acceptHeader)) {
                return true;
            }
        }

        // Check for the presence of the custom API version header
        $customHeader = $config['custom_header'];

        return is_string($customHeader) && $request->hasHeader($customHeader);
    }
}
