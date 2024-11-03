<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

// Configure API detection headers
beforeEach(function () {
    Config::set('api-version.default_version', 'v1');
    Config::set('api-version.use_accept_header', true);
    Config::set('api-version.custom_header', 'X-API-Version');
    Config::set('api-version.accept_header_pattern', '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/');

    Route::get('/api/test', function () {
        abort(404);
    });

    Route::get('/web/test', function () {
        abort(404);
    });
});

it('returns JSON 404 response with API version for API requests', function () {
    // Simulate API request with Accept header pattern match
    $response = $this->getJson('/api/test', [
        'Accept' => 'application/vnd.yourapp+v2+json',
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'error' => 'The requested resource does not exist in this API version.',
            'version' => 'v2',
        ]);
});

it('returns JSON 404 response with default version for API request when version not specified', function () {
    // Simulate API request with custom header for version detection
    $response = $this->getJson('/api/test', [
        'X-API-Version' => '3',
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'error' => 'The requested resource does not exist in this API version.',
            'version' => 'v3',
        ]);
});

it('returns 404 status code with default response if no API version is provided in headers', function () {
    // Simulate API request without any version information in headers
    $response = $this->getJson('/api/test');

    $response->assertStatus(404)
        ->assertJson([
            'message' => '',
        ]);
});

it('falls back to default 404 handler for non-API requests', function () {
    // Simulate non-API request
    $response = $this->get('/web/test');

    $response->assertStatus(404)
        ->assertDontSee('The requested resource does not exist in this API version.');
});
