[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/laravel-api-version.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/laravel-api-version) [![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/laravel-api-version/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cleaniquecoders/laravel-api-version/actions?query=workflow%3Arun-tests+branch%3Amain) [![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/laravel-api-version/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cleaniquecoders/laravel-api-version/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain) [![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/laravel-api-version.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/laravel-api-version)

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

Here’s the updated **Usage** section for your `README.md`, reflecting the ability to explicitly set API versions for routes and using the `api.version` middleware.

---

## Usage

### Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="laravel-api-version-config"
```

This creates a `config/api-version.php` file where you can customize options like the default version, custom headers, version format, and the root namespace for versioned controllers.

Sample configuration:

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

Ensure the `api.version` middleware is registered by default when the package is loaded. This middleware automatically detects API versions based on headers or explicitly defined versions.

### Defining Versioned Routes

#### Option 1: Header-Based Version Detection

In your `routes/api.php`, use the `api.version` middleware to enable automatic version detection from headers:

```php
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'api.version'])->group(function () {
    Route::get('/example', 'ExampleController@index');
    // Define additional routes here
});
```

With this setup, the middleware will look for version information in the `Accept` or `X-API-Version` headers and dynamically route requests to the correct versioned namespace.

#### Option 2: Explicitly Setting the Version

You can explicitly specify the version for a route or route group by passing the version as a parameter to the middleware. This method overrides any version information in headers.

```php
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'api.version:v1'])->group(function () {
    Route::get('/example', 'ExampleController@index');
    // Additional routes for v1
});

Route::middleware(['api', 'api.version:v2'])->group(function () {
    Route::get('/example', 'ExampleController@index');
    // Additional routes for v2
});
```

In this example:

- The `api.version:v1` middleware directs all routes within the group to the `v1` namespace.
- Similarly, `api.version:v2` directs routes to the `v2` namespace, bypassing any header detection.

### Example Requests

**Using `Accept` Header**:

```bash
curl -L \
  -H "Accept: application/vnd.yourapp+v2+json" \
  https://yourapp/api/example
```

**Using Custom Header (`X-API-Version`)**:

```bash
curl -L \
  -H "X-API-Version: 2" \
  https://yourapp/api/example
```

**Explicitly Versioned Route**:

If you specify `api.version:v2` in the route definition, no header is needed to access version 2, as the route explicitly defines it.

---

This setup provides flexibility, allowing you to choose between header-based detection and explicit versioning based on your API design needs.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Nasrul Hazim Bin Mohamad](https://github.com/nasrulhazim)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
