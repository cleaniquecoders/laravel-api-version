# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Package

`cleaniquecoders/laravel-api-version` — Laravel package for header-based API versioning. Detects versions from `Accept` or `X-API-Version` headers and routes requests to version-specific controller namespaces (e.g., `v2` → `App\Http\Controllers\Api\V2`).

Supports Laravel 10/11/12 on PHP 8.2–8.4.

## Commands

```bash
composer test              # Run Pest tests
composer test -- --filter=SomeTest  # Run a single test file/filter
composer test-coverage     # Run tests with coverage
composer analyse           # PHPStan at level max (src/ and config/)
composer format            # Laravel Pint code formatting
composer prepare           # Register package in testbench
composer start             # Build workbench and serve locally
```

## Architecture

**Request flow:** `ApiVersion` middleware → `VersionResolver::resolve()` (extracts version from headers) → `ControllerResolver::resolveNamespace()` (maps version to namespace) → route executes with versioned controller.

### Key source files

- `src/Http/Middleware/ApiVersion.php` — Core middleware; resolves version, stores it in `$request->attributes`, adds response headers, triggers deprecation headers
- `src/Processors/VersionResolver.php` — Extracts version from Accept header, custom header, or middleware parameter; validates format and supported versions; caches config in static property
- `src/Processors/ControllerResolver.php` — Maps version string to PHP namespace (dots→underscores); caches resolved namespaces (1-hour TTL)
- `src/Processors/DeprecationProcessor.php` — Adds RFC-compliant deprecation headers (`Deprecation`, `Sunset`, `Link`)
- `src/Exceptions/ApiExceptionHandler.php` — Replaces Laravel's handler; returns JSON 404 for API requests
- `src/LaravelApiVersionServiceProvider.php` — Registers `api.version` middleware alias, replaces exception handler, validates config at boot
- `config/api-version.php` — All configuration: default version, headers, regex pattern, namespace, supported/deprecated versions

### Conventions

- **Processors** use stateless static methods — no constructor injection
- **Version format**: always `v`-prefixed (e.g., `v1`, `v2.1`); normalized automatically
- **Namespace mapping**: `v2.1` → `V2_1` subdirectory under `root_namespace`
- **Middleware usage**: `api.version` (header detection) or `api.version:v2` (explicit)
- Resolved version accessible via `$request->attributes->get('api_version')`

### Testing

- Pest PHP with Orchestra Testbench as base (`tests/TestCase.php`)
- Tests register routes in `beforeEach()` hooks, then assert version resolution via HTTP requests
- Architecture tests in `tests/ArchTest.php` using Pest Arch plugin
- Always test both explicit version parameter and header-based resolution paths

### CI Matrix

GitHub Actions runs tests across PHP 8.3–8.4 × Laravel 10–12, plus separate PHPStan and Pint workflows.
