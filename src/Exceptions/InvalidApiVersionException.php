<?php

namespace CleaniqueCoders\LaravelApiVersion\Exceptions;

use InvalidArgumentException;

class InvalidApiVersionException extends InvalidArgumentException
{
    public static function invalidFormat(string $version): self
    {
        return new self("Invalid API version format: '{$version}'. Expected format: v1, v2, v1.1, etc.");
    }

    /**
     * @param  array<string>  $supportedVersions
     */
    public static function unsupportedVersion(string $version, array $supportedVersions = []): self
    {
        $supported = empty($supportedVersions) ? 'No supported versions configured.' : 'Supported versions: '.implode(', ', $supportedVersions);

        return new self("Unsupported API version: '{$version}'. {$supported}");
    }
}
