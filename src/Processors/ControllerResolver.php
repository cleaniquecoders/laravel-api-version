<?php

namespace CleaniqueCoders\LaravelApiVersion\Processors;

use Illuminate\Support\Facades\Config;

class ControllerResolver
{
    /**
     * Resolve the controller namespace based on the API version.
     *
     * @param  string  $version
     * @return string
     */
    public static function resolveNamespace(string $version): string
    {
        $rootNamespace = Config::get('api-version.root_namespace', 'App\Http\Controllers\Api');

        // Ensure rootNamespace is a string, or fallback to default
        if (!is_string($rootNamespace)) {
            $rootNamespace = 'App\Http\Controllers\Api';
        }

        return rtrim($rootNamespace, '\\') . '\\' . ucfirst($version);
    }
}
