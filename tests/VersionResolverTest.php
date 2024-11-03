<?php

use CleaniqueCoders\LaravelApiVersion\Processors\VersionResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    // Set default configuration values
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

it('resolves version from Accept header when it matches the pattern', function () {
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/vnd.yourapp+v2+json']);
    $version = VersionResolver::resolve($request);

    expect($version)->toBe('v2');
});

it('falls back to custom header when Accept header is not provided or does not match', function () {
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_X_API_VERSION' => '3']);
    $version = VersionResolver::resolve($request);

    expect($version)->toBe('v3');
});

it('uses default version when Accept header does not match pattern and custom header is absent', function () {
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'text/html']);
    $version = VersionResolver::resolve($request);

    expect($version)->toBe('v1');
});

it('identifies request as API request based on Accept header with correct pattern', function () {
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/vnd.yourapp+v2+json']);
    $isApiRequest = VersionResolver::isApiRequest($request);

    expect($isApiRequest)->toBeTrue();
});

it('identifies request as API request based on custom header', function () {
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_X_API_VERSION' => '2']);
    $isApiRequest = VersionResolver::isApiRequest($request);

    expect($isApiRequest)->toBeTrue();
});

it('does not identify request as API request when headers do not match config settings', function () {
    $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'text/html']);
    $isApiRequest = VersionResolver::isApiRequest($request);

    expect($isApiRequest)->toBeFalse();
});
