<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default API Version
    |--------------------------------------------------------------------------
    |
    | This option controls the default version of your API that will be used
    | when no version is specified in the request headers. Define your
    | API versions here as strings, such as 'v1', 'v2', etc.
    |
    */

    'default_version' => 'v1',

    /*
    |--------------------------------------------------------------------------
    | Header-Based Versioning
    |--------------------------------------------------------------------------
    |
    | Define how your application handles API versioning through headers.
    | By default, the package checks the "Accept" header with a structure
    | like "application/vnd.yourapp+v1.0+json" to extract versioning info.
    | If not provided, a custom header will be used as a fallback.
    |
    */

    'use_accept_header' => true,

    /*
    |--------------------------------------------------------------------------
    | Custom Header Name
    |--------------------------------------------------------------------------
    |
    | Define the custom header used to specify the API version when the
    | "Accept" header is not provided or is not in the required format.
    | The default custom header is "X-API-Version".
    |
    | Examples:
    |   - X-API-Version: 1.0
    |   - X-API-Version: v1
    |
    */

    'custom_header' => 'X-API-Version',

    /*
    |--------------------------------------------------------------------------
    | Version Format
    |--------------------------------------------------------------------------
    |
    | This setting controls the format expected in the versioning header,
    | particularly for the "Accept" header. Customize this regex pattern
    | based on your versioning scheme. By default, it captures versions
    | like "application/vnd.yourapp+v1.0+json".
    |
    */

    'accept_header_pattern' => '/application\/vnd\.\w+\+v(\d+(\.\d+)*)\+json/',

];
