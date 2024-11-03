<?php

use CleaniqueCoders\LaravelApiVersion\Processors\VersionResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('api-version.default_version', 'v1');
    Config::set('api-version.use_accept_header', true);
    Config::set('api-version.custom_header', 'X-API-Version');
    Config::set('api-version.accept_header_pattern', '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/');
});

it('returns default version if no header is set', function () {
    $request = Request::create('/', 'GET');
    $version = VersionResolver::resolve($request);
    expect($version)->toBe('v1');
});

it('resolves version from Accept header', function () {
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/vnd.yourapp+v2+json']);
    $version = VersionResolver::resolve($request);
    expect($version)->toBe('v2');
});

it('falls back to custom header when Accept header is not provided', function () {
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_X_API_VERSION' => '3']);
    $version = VersionResolver::resolve($request);
    expect($version)->toBe('v3');
});
