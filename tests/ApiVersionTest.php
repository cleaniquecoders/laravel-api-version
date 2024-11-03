<?php

use CleaniqueCoders\LaravelApiVersion\Http\Middleware\ApiVersion;
use CleaniqueCoders\LaravelApiVersion\Processors\ControllerResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Set up a test route with the ApiVersion middleware
beforeEach(function () {
    Route::middleware(ApiVersion::class)->get('/api/example', function (Request $request) {
        return response()->json([
            'version' => $request->attributes->get('api_version'),
            'namespace' => ControllerResolver::resolveNamespace($request->attributes->get('api_version')),
        ]);
    });
});

it('applies default version and namespace if no version header is present', function () {
    $this->get('/api/example')
        ->assertJson([
            'version' => 'v1',
            'namespace' => 'App\Http\Controllers\Api\V1',
        ]);
});

it('resolves version and namespace from Accept header', function () {
    $this->getJson('/api/example', ['Accept' => 'application/vnd.yourapp+v2+json'])
        ->assertJson([
            'version' => 'v2',
            'namespace' => 'App\Http\Controllers\Api\V2',
        ]);
});

it('resolves version and namespace from custom header when Accept header is not provided', function () {
    $this->getJson('/api/example', ['X-API-Version' => '3'])
        ->assertJson([
            'version' => 'v3',
            'namespace' => 'App\Http\Controllers\Api\V3',
        ]);
});
