# Changelog

All notable changes to `laravel-api-version` will be documented in this file.

## v1.0.1 - 2024-11-03

### Release Notes for Version 1.0.1

**Release Date**: *2024-11-03*

**Full Changelog**: https://github.com/cleaniquecoders/laravel-api-version/compare/v1.0.0...v1.0.1


---

#### Added

- **Enhanced Exception Handling**: Improved `ApiExceptionHandler` to dynamically use `VersionResolver` for detecting the current API version based on headers, ensuring consistent responses across endpoints.
- **Flexible Version Detection**: Added support for detecting API requests using either the `Accept` header or a custom header (`X-API-Version`), configurable in `config/api-version.php`.
- **Explicit API Version Control**: Middleware now supports explicitly defined API versions within routes, allowing developers to bypass header detection when necessary.

#### Fixed

- **Compatibility with Testing Frameworks**: Resolved issues in `VersionResolverTest` and `ApiExceptionHandlerTest` to ensure compatibility with PestPHP, addressing errors related to header handling and API request identification.
- **PHPStan Compliance**: Cleaned up code to meet PHPStan level 8 standards, fixing type-checking issues and unreachable code branches.

#### Updated

- **Documentation**: Enhanced README with detailed usage examples for both header-based and explicit version detection, along with sample `curl` commands for testing.

## v1.0.0 - 2024-11-03

**Full Changelog**: https://github.com/cleaniquecoders/laravel-api-version/commits/v1.0.0
