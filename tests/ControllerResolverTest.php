<?php

use CleaniqueCoders\LaravelApiVersion\Processors\ControllerResolver;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('api-version.root_namespace', 'App\Http\Controllers\Api');
});

it('resolves the default namespace for v1', function () {
    $namespace = ControllerResolver::resolveNamespace('v1');
    expect($namespace)->toBe('App\Http\Controllers\Api\V1');
});

it('resolves a custom namespace for v2', function () {
    Config::set('api-version.root_namespace', 'App\Http\Controllers\CustomApi');
    $namespace = ControllerResolver::resolveNamespace('v2');
    expect($namespace)->toBe('App\Http\Controllers\CustomApi\V2');
});
