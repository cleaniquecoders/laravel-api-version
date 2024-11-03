<?php

namespace CleaniqueCoders\LaravelApiVersion\Http\Middleware;

use CleaniqueCoders\LaravelApiVersion\Processors\ControllerResolver;
use CleaniqueCoders\LaravelApiVersion\Processors\VersionResolver;
use Closure;
use Illuminate\Http\Request;

class ApiVersion
{
    /**
     * Handle the incoming request and apply API versioning.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Resolve the API version
        $version = VersionResolver::resolve($request);

        // Store the API version in the request
        $request->attributes->set('api_version', $version);

        // Use ControllerResolver to get the namespace for this version
        $namespace = ControllerResolver::resolveNamespace($version);

        // Apply the dynamically resolved namespace
        $this->applyNamespace($namespace, $request, $next);

        return $next($request);
    }

    /**
     * Apply the resolved namespace to the router group.
     *
     * @return void
     */
    protected function applyNamespace(string $namespace, Request $request, Closure $next)
    {
        // Dynamically modify the router group namespace
        app()->make('router')->group(['namespace' => $namespace], function () use ($request, $next) {
            return $next($request);
        });
    }
}
