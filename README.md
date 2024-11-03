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

## Usage

### Configuration

To start, publish the configuration file:

```bash
php artisan vendor:publish --tag="laravel-api-version-config"
```

This will create a `config/api-version.php` file. Here, you can customize options like the default version, custom headers, version format, and the root namespace for versioned controllers.

Example of the configuration file:

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

Add the `ApiVersion` middleware to the `$routeMiddleware` array in `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    'api.version' => \CleaniqueCoders\LaravelApiVersion\Http\Middleware\ApiVersion::class,
];
```

Now, you can apply the `api.version` middleware to your routes.

### Defining Versioned Routes

In your `routes/api.php`, use the `api.version` middleware on routes that should be versioned. The middleware will dynamically map requests to the correct controller namespace based on the version detected in the headers.

```php
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'api.version'])->group(function () {
    Route::get('/example', 'ExampleController@index');
    // Define additional routes here
});
```

With this setup, requests will automatically route to controllers within the appropriate versioned namespace, such as `App\Http\Controllers\Api\V1\ExampleController` for `v1` or `App\Http\Controllers\Api\V2\ExampleController` for `v2`.

### Example Request

Using the `Accept` header:

```bash
curl -L \
  -H "Accept: application/vnd.yourapp+v2+json" \
  https://yourapp/api/example
```

Using the custom header (`X-API-Version`):

```bash
curl -L \
  -H "X-API-Version: 2" \
  https://yourapp/api/example
```

This will route requests to the correct versioned controller (`V2\ExampleController`) as specified by the headers.

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
