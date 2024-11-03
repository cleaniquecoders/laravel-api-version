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
        $defaultVersion = Config::get('api-version.default_version', 'v1');
        $useAcceptHeader = Config::get('api-version.use_accept_header', true);
        $customHeader = Config::get('api-version.custom_header', 'X-API-Version');
        $acceptHeaderPattern = Config::get('api-version.accept_header_pattern', '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/');

        // Start with default version
        $version = $defaultVersion;

        // Check Accept header if enabled
        if ($useAcceptHeader && $request->hasHeader('Accept')) {
            $acceptHeader = $request->header('Accept');
            if (preg_match($acceptHeaderPattern, $acceptHeader, $matches)) {
                $version = 'v'.$matches[1];
            }
        }

        // Fallback to custom header if version is still default
        if ($version === $defaultVersion && $request->hasHeader($customHeader)) {
            $version = 'v'.$request->header($customHeader);
        }

        return $version;
    }
}
