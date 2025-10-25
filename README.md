[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/laravel-api-version.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/laravel-api-version) [![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/laravel-api-version/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cleaniquecoders/laravel-api-version/actions?query=workflow%3Arun-tests+branch%3Amain) [![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/laravel-api-version/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cleaniquecoders/laravel-api-version/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain) [![PHPStan](https://github.com/cleaniquecoders/laravel-api-version/actions/workflows/phpstan.yml/badge.svg)](https://github.com/cleaniquecoders/laravel-api-version/actions/workflows/phpstan.yml) [![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/laravel-api-version.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/laravel-api-version)

# Laravel API Version

Effortlessly manage your Laravel API versions with flexible, header-based versioning control, comprehensive validation, deprecation management, and enterprise-ready features.

## Features

✨ **Header-based Version Detection** - Automatic version resolution from `Accept` or custom headers
🛡️ **Comprehensive Validation** - Boot-time configuration validation and runtime version validation
📅 **Deprecation Management** - Built-in support for API version deprecation with proper HTTP headers
⚡ **Performance Optimized** - Caching for namespace resolution and configuration
🎯 **Explicit Version Control** - Support for both automatic detection and explicit version specification
🔧 **Enterprise Ready** - Production-quality error handling and monitoring capabilities

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
  - [Configuration](#configuration)
  - [Middleware Setup](#middleware-setup)
  - [Defining Versioned Routes](#defining-versioned-routes)
  - [Example Requests](#example-requests)
- [Advanced Features](#advanced-features)
  - [Version Validation](#version-validation)
  - [Deprecation Management](#deprecation-management)
  - [Version Format Support](#version-format-support)
  - [Controller Namespace Mapping](#controller-namespace-mapping)
  - [Performance Optimizations](#performance-optimizations)
  - [Error Handling](#error-handling)
- [Configuration Reference](#configuration-reference)
- [Testing](#testing)
- [Migration Guide](#migration-guide)
- [Troubleshooting](#troubleshooting)

## Quick Start

1. **Install the package:**

   ```bash
   composer require cleaniquecoders/laravel-api-version
   ```

2. **Publish configuration:**

   ```bash
   php artisan vendor:publish --tag="laravel-api-version-config"
   ```

3. **Set up routes with versioning:**

   ```php
   Route::middleware(['api', 'api.version'])->group(function () {
       Route::get('/users', 'UserController@index');
   });
   ```

4. **Create versioned controllers:**

   ```text
   app/Http/Controllers/Api/V1/UserController.php
   app/Http/Controllers/Api/V2/UserController.php
   ```

5. **Make requests with version headers:**

   ```bash
   curl -H "X-API-Version: 2" https://yourapp/api/users
   ```

## Installation

You can install the package via composer:

```bash
composer require cleaniquecoders/laravel-api-version
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-api-version-config"
```

## Usage

### Configuration

This will create a `config/api-version.php` file where you can customize options like the default version, headers, version format, supported versions, deprecation settings, and the root namespace for versioned controllers.

Example configuration:

```php
return [
    // Basic Configuration
    'default_version' => 'v1',
    'use_accept_header' => true,
    'custom_header' => 'X-API-Version',
    'accept_header_pattern' => '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/',
    'root_namespace' => 'App\Http\Controllers\Api',

    // Version Management
    'supported_versions' => [
        'v1', 'v2', 'v3'
    ],

    // Deprecation Management
    'deprecated_versions' => [
        'v1' => [
            'sunset_date' => '2024-12-31',
            'replacement' => 'v2',
            'message' => 'API v1 is deprecated. Please migrate to v2.',
        ],
    ],
];
```

### Key Configuration Options

- **`supported_versions`** - Whitelist of allowed API versions (empty array allows all)
- **`deprecated_versions`** - Configure deprecation warnings with sunset dates and replacement versions
- **Version validation** - Automatic validation of version formats and supported versions
- **Performance caching** - Built-in caching for namespace resolution and configuration

### Middleware Setup

The `api.version` middleware is registered automatically. This middleware provides:

- 🔍 **Automatic version detection** from headers or explicit specification
- ✅ **Comprehensive validation** with helpful error messages
- 📊 **Response headers** including `X-API-Version` and deprecation warnings
- 🚀 **Performance optimization** through caching

### Defining Versioned Routes

#### Option 1: Header-Based Version Detection

To enable automatic version detection from headers, use the `api.version` middleware in your `routes/api.php`:

```php
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'api.version'])->group(function () {
    Route::get('/example', 'ExampleController@index');
    // Additional routes here
});
```

This setup detects versions from the `Accept` or `X-API-Version` headers, dynamically routing requests to the correct versioned namespace.

#### Option 2: Explicitly Setting the Version

You can explicitly define a version for a route or route group by passing the version to the middleware. This approach bypasses header detection.

```php
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'api.version:v1'])->group(function () {
    Route::get('/example', 'ExampleController@index');
    // Routes for v1
});

Route::middleware(['api', 'api.version:v2'])->group(function () {
    Route::get('/example', 'ExampleController@index');
    // Routes for v2
});
```

In this example:

- `api.version:v1` directs routes in the group to the `v1` namespace.
- `api.version:v2` directs routes to the `v2` namespace, ignoring headers.

### Example Requests

#### Using `Accept` Header

```bash
curl -L -H "Accept: application/vnd.yourapp+v2+json" https://yourapp/api/example
```

#### Using Custom Header (`X-API-Version`)

```bash
curl -L -H "X-API-Version: 2" https://yourapp/api/example
```

#### Explicitly Versioned Route

If the route is explicitly defined as `api.version:v2`, no header is needed to access version 2.

## Advanced Features

### Version Validation

The package provides comprehensive version validation:

```php
// Automatically validates version format (v1, v2, v1.1, etc.)
// Checks against supported_versions if configured
// Provides clear error messages for invalid versions

// Example error response for invalid version:
{
    "error": "Invalid API version",
    "message": "Unsupported API version: 'v5'. Supported versions: v1, v2, v3",
    "supported_versions": ["v1", "v2", "v3"]
}
```

### Deprecation Management

When using deprecated versions, the API automatically adds appropriate headers:

```http
HTTP/1.1 200 OK
X-API-Version: v1
Deprecation: true
Sunset: 2024-12-31
Link: <https://api.example.com/v2>; rel="successor-version"
X-API-Deprecation-Message: API v1 is deprecated. Please migrate to v2.
```

### Version Format Support

The package supports various version formats:

- `v1`, `v2`, `v3` - Simple versioning
- `v1.1`, `v2.5` - Minor versions
- `v1.0.1` - Patch versions
- Automatic normalization (e.g., `1` becomes `v1`)

### Controller Namespace Mapping

Versions are automatically mapped to controller namespaces:

- `v1` → `App\Http\Controllers\Api\V1`
- `v2.1` → `App\Http\Controllers\Api\V2_1`
- `v3.0.1` → `App\Http\Controllers\Api\V3_0_1`

### Performance Optimizations

- ⚡ **Configuration Caching** - Reduces repeated config() calls
- 🗄️ **Namespace Caching** - Caches namespace resolution results
- 🔄 **Cache Management** - Automatic cache invalidation and testing support

### Error Handling

The package provides robust error handling:

- **Configuration Validation** - Validates config at boot time
- **Runtime Validation** - Validates versions during request processing
- **Clear Error Messages** - Helpful error responses for debugging
- **Exception Types** - Custom exceptions for different error scenarios

## Configuration Reference

### Complete Configuration Example

```php
return [
    // Required: Default version when none specified
    'default_version' => 'v1',

    // Header Configuration
    'use_accept_header' => true,
    'custom_header' => 'X-API-Version',
    'accept_header_pattern' => '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/',

    // Namespace Configuration
    'root_namespace' => 'App\Http\Controllers\Api',

    // Version Management
    'supported_versions' => [
        'v1', 'v2', 'v3'
    ],

    // Deprecation Configuration
    'deprecated_versions' => [
        'v1' => [
            'sunset_date' => '2024-12-31',        // RFC 7234 Sunset header
            'replacement' => 'v2',                // Replacement version
            'message' => 'API v1 is deprecated.', // Custom message
        ],
        'v2' => [
            'sunset_date' => '2025-06-30',
            'replacement' => 'v3',
            'message' => 'API v2 will be sunset on June 30, 2025.',
        ],
    ],
];
```

## Testing

Run the package tests:

```bash
composer test
```

Run with coverage:

```bash
composer test-coverage
```

Run static analysis:

```bash
composer analyse
```

The package includes comprehensive tests covering:

- ✅ Version resolution and validation
- ✅ Configuration validation
- ✅ Deprecation functionality
- ✅ Performance optimizations
- ✅ Error handling scenarios
- ✅ Middleware integration

## Migration Guide

### From v1.x to v2.x

The package maintains backward compatibility, but you can take advantage of new features:

1. **Add version validation** by configuring `supported_versions`
2. **Set up deprecation warnings** using `deprecated_versions`
3. **Update error handling** to catch `InvalidApiVersionException`
4. **Review performance** improvements with built-in caching

### Upgrading Controllers

No changes required to existing controllers. The enhanced namespace mapping automatically handles:

```php
// v1 controllers remain in: App\Http\Controllers\Api\V1
// v2.1 controllers work in: App\Http\Controllers\Api\V2_1
```

## Troubleshooting

### Common Issues

**Invalid version format errors:**

```php
// ❌ Wrong
'supported_versions' => ['1', '2', 'invalid']

// ✅ Correct
'supported_versions' => ['v1', 'v2', 'v3']
```

**Configuration validation errors:**

- Ensure `default_version` is included in `supported_versions` when specified
- Verify regex patterns in `accept_header_pattern` are valid
- Check that `root_namespace` points to existing namespace

**Performance considerations:**

- Use `supported_versions` to limit valid versions
- Configure appropriate cache TTL for your environment
- Monitor deprecation header overhead for high-traffic APIs

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for details on recent updates.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for contribution guidelines.

## Security Vulnerabilities

Please review our [security policy](../../security/policy) for reporting security issues.

## Credits

- [Nasrul Hazim Bin Mohamad](https://github.com/nasrulhazim)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). See the [License File](LICENSE.md) for details.
