<?php

namespace CleaniqueCoders\LaravelApiVersion\Processors;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class VersionResolver
{
    /**
     * Resolve the API version based on headers and config.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    public static function resolve(Request $request): string
    {
        $defaultVersion = Config::get('api-version.default_version', 'v1');
        $useAcceptHeader = Config::get('api-version.use_accept_header', true);
        $customHeader = Config::get('api-version.custom_header', 'X-API-Version');
        $acceptHeaderPattern = Config::get('api-version.accept_header_pattern', '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/');

        // Ensure defaultVersion, customHeader, and acceptHeaderPattern are strings
        $defaultVersion = is_string($defaultVersion) ? $defaultVersion : 'v1';
        $customHeader = is_string($customHeader) ? $customHeader : 'X-API-Version';
        $acceptHeaderPattern = is_string($acceptHeaderPattern) ? $acceptHeaderPattern : '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/';

        $version = $defaultVersion;

        // Check Accept header if enabled
        if ($useAcceptHeader && $request->hasHeader('Accept')) {
            $acceptHeader = $request->header('Accept', '');
            if (is_string($acceptHeader) && preg_match($acceptHeaderPattern, $acceptHeader, $matches)) {
                $version = 'v' . ($matches[1] ?? ''); // Ensure matches[1] exists
            }
        }

        // Fallback to custom header if version is still default
        if ($version === $defaultVersion && $request->hasHeader($customHeader)) {
            $headerVersion = $request->header($customHeader, '');
            $version = 'v' . $headerVersion; // $headerVersion is guaranteed to be a string
        }

        return $version;
    }
}
