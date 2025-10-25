<?php

namespace CleaniqueCoders\LaravelApiVersion\Http\Middleware;

use CleaniqueCoders\LaravelApiVersion\Exceptions\InvalidApiVersionException;
use CleaniqueCoders\LaravelApiVersion\Processors\ControllerResolver;
use CleaniqueCoders\LaravelApiVersion\Processors\DeprecationProcessor;
use CleaniqueCoders\LaravelApiVersion\Processors\VersionResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiVersion
{
    /**
     * Handle the incoming request and apply API versioning.
     */
    public function handle(Request $request, Closure $next, ?string $explicitVersion = null): Response
    {
        try {
            // Resolve version using explicit version or headers
            $version = VersionResolver::resolve($request, $explicitVersion);

            // Store the API version in the request
            $request->attributes->set('api_version', $version);

            // Use ControllerResolver to get the namespace for this version
            $namespace = ControllerResolver::resolveNamespace($version);
            $request->attributes->set('api_namespace', $namespace);

            // Apply the dynamically resolved namespace
            $this->applyNamespace($namespace, $request, $next);

            $response = $next($request);

            // Add version header to response
            if (method_exists($response, 'header')) {
                $response->header('X-API-Version', $version);

                // Add deprecation headers if the version is deprecated
                $deprecationHeaders = DeprecationProcessor::getDeprecationHeaders($version);
                foreach ($deprecationHeaders as $headerName => $headerValue) {
                    $response->header($headerName, $headerValue);
                }
            }

            return $response;

        } catch (InvalidApiVersionException $e) {
            return response()->json([
                'error' => 'Invalid API version',
                'message' => $e->getMessage(),
                'supported_versions' => config('api-version.supported_versions', []),
            ], 400);
        }
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
