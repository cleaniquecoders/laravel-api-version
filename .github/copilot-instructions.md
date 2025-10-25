# Laravel API Version Package - AI Coding Instructions

## Project Overview
This is a Laravel package that provides flexible API versioning through header-based routing. The package automatically detects API versions from `Accept` or `X-API-Version` headers and routes requests to version-specific controller namespaces.

## Core Architecture

### Version Resolution Flow
1. **Headers → Version Detection**: `VersionResolver::resolve()` extracts version from headers
2. **Version → Namespace Mapping**: `ControllerResolver::resolveNamespace()` maps version to controller namespace
3. **Middleware Application**: `ApiVersion` middleware applies the resolved namespace to routes

### Key Components
- **`ApiVersion` middleware**: Core entry point that resolves version and applies namespace routing
- **`VersionResolver`**: Handles version extraction from `Accept` header (`application/vnd.app+v2+json`) or `X-API-Version` header
- **`ControllerResolver`**: Maps versions (e.g., `v2`) to controller namespaces (e.g., `App\Http\Controllers\Api\V2`)
- **`ApiExceptionHandler`**: Provides version-aware 404 responses for API requests

### Configuration Patterns
All configuration is in `config/api-version.php`:
- `default_version`: Fallback version (default: `v1`)
- `root_namespace`: Base controller namespace (default: `App\Http\Controllers\Api`)
- `accept_header_pattern`: Regex for parsing Accept header versions
- `custom_header`: Fallback header name (default: `X-API-Version`)

## Development Workflows

### Testing
- **Test runner**: `composer test` (uses Pest PHP)
- **Test structure**: Feature tests in `tests/` using Orchestra Testbench
- **Test patterns**: Each test sets up routes with middleware, then asserts version resolution
- **Coverage**: `composer test-coverage`

### Code Quality
- **Static analysis**: `composer analyse` (PHPStan with Laravel extensions)
- **Code formatting**: `composer format` (Laravel Pint)
- **Architecture testing**: Uses Pest plugin for architectural constraints

### Local Development
- **Workbench**: Uses `workbench/` directory for testing package integration
- **Package discovery**: `composer prepare` registers package in testbench
- **Local server**: `composer start` builds and serves test environment

## Package-Specific Conventions

### Namespace Conventions
- **Processor classes**: Logic components in `src/Processors/` (stateless static methods)
- **Exception handling**: Custom handlers in `src/Exceptions/` extend Laravel's base handlers
- **Service provider**: Single provider registers middleware alias and exception handler

### Version Format Standards
- **Version prefix**: All versions prefixed with `v` (e.g., `v1`, `v2`, `v1.1`)
- **Controller namespaces**: Versions become PascalCase namespace segments (`v2` → `V2`)
- **Header parsing**: Accept header uses vendor-specific format with version embedded

### Testing Patterns
- **Middleware testing**: Set up routes with middleware, test header combinations
- **Processor testing**: Unit tests for static resolver methods with various inputs
- **Integration testing**: Full request flow testing with different version scenarios

## Critical Implementation Details

### Middleware Registration
The package automatically registers `api.version` middleware alias in the service provider. Use either:
- `Route::middleware(['api.version'])` for header-based detection
- `Route::middleware(['api.version:v2'])` for explicit version specification

### Request Attribute Storage
Resolved version is stored in `$request->attributes->set('api_version', $version)` for downstream access.

### Exception Handling Override
The package replaces Laravel's default exception handler with `ApiExceptionHandler` to provide version-aware API responses.

## When Adding Features
- **New processors**: Follow stateless static method pattern in `src/Processors/`
- **Configuration changes**: Update `config/api-version.php` and add corresponding tests
- **Middleware extensions**: Consider version storage and namespace resolution impacts
- **Testing**: Always test both explicit version and header-based resolution paths
