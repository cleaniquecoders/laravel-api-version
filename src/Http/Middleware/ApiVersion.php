<?php

namespace CleaniqueCoders\LaravelApiVersion\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use CleaniqueCoders\LaravelApiVersion\Processors\VersionResolver;
use CleaniqueCoders\LaravelApiVersion\Processors\ControllerResolver;

class ApiVersion
{
    /**
     * Handle the incoming request and apply API versioning.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $version
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $version = null)
    {
        // If a version is explicitly set in the middleware, use it
        if ($version === null) {
            $version = VersionResolver::resolve($request); // Fallback to resolver if no version is provided
        }

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
     * @param string $namespace
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
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
