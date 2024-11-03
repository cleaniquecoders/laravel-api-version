[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/laravel-api-version.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/laravel-api-version) [![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/laravel-api-version/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cleaniquecoders/laravel-api-version/actions?query=workflow%3Arun-tests+branch%3Amain) [![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/laravel-api-version/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cleaniquecoders/laravel-api-version/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain) [![PHPStan](https://github.com/cleaniquecoders/laravel-api-version/actions/workflows/phpstan.yml/badge.svg)](https://github.com/cleaniquecoders/laravel-api-version/actions/workflows/phpstan.yml) [![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/laravel-api-version.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/laravel-api-version)

# Laravel API Version

Effortlessly manage your Laravel API versions with flexible, header-based versioning control.

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

This will create a `config/api-version.php` file where you can customize options like the default version, headers, version format, and the root namespace for versioned controllers.

Example configuration:

```php
return [
    'default_version' => 'v1',
    'use_accept_header' => true,
    'custom_header' => 'X-API-Version',
    'accept_header_pattern' => '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/',
    'root_namespace' => 'App\Http\Controllers\Api',
];
```

### Middleware Setup

The `api.version` middleware is registered automatically. This middleware allows for automatic version detection based on headers or explicit version specification in the route.

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

## Testing

Run the package tests:

```bash
composer test
```

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
