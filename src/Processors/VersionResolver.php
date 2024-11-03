<?php

namespace CleaniqueCoders\LaravelApiVersion\Processors;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class VersionResolver
{
    /**
     * Resolve the API version based on headers and config.
     */
    public static function resolve(Request $request): string
    {
        // Retrieve configuration values and ensure they are strings
        $defaultVersion = Config::get('api-version.default_version', 'v1');
        $defaultVersion = is_string($defaultVersion) ? $defaultVersion : 'v1';

        $useAcceptHeader = Config::get('api-version.use_accept_header', true);

        $customHeader = Config::get('api-version.custom_header', 'X-API-Version');
        $customHeader = is_string($customHeader) ? $customHeader : 'X-API-Version';

        $acceptHeaderPattern = Config::get('api-version.accept_header_pattern', '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/');
        $acceptHeaderPattern = is_string($acceptHeaderPattern) ? $acceptHeaderPattern : '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/';

        $version = $defaultVersion;

        // Check Accept header if enabled
        if ($useAcceptHeader && $request->hasHeader('Accept')) {
            $acceptHeader = $request->header('Accept', '');
            if (is_string($acceptHeader) && preg_match($acceptHeaderPattern, $acceptHeader, $matches)) {
                $version = 'v'.($matches[1] ?? ''); // Use matched version if available
            }
        }

        // Fallback to custom header if version is still default
        if ($version === $defaultVersion && $request->hasHeader($customHeader)) {
            $version = 'v'.$request->header($customHeader, ''); // Direct assignment without ternary
        }

        return $version;
    }

    /**
     * Determine if the request qualifies as an API request.
     */
    public static function isApiRequest(Request $request): bool
    {
        $useAcceptHeader = Config::get('api-version.use_accept_header', true);

        $customHeader = Config::get('api-version.custom_header', 'X-API-Version');
        $customHeader = is_string($customHeader) ? $customHeader : 'X-API-Version';

        $acceptHeaderPattern = Config::get('api-version.accept_header_pattern', '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/');
        $acceptHeaderPattern = is_string($acceptHeaderPattern) ? $acceptHeaderPattern : '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/';

        // Check Accept header pattern for API version if enabled
        if ($useAcceptHeader && $request->hasHeader('Accept')) {
            $acceptHeader = $request->header('Accept', '');
            if (is_string($acceptHeader) && preg_match($acceptHeaderPattern, $acceptHeader)) {
                return true;
            }
        }

        // Check for the presence of the custom API version header
        return $request->hasHeader($customHeader);
    }
}
