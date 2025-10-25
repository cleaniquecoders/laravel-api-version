<?php

namespace CleaniqueCoders\LaravelApiVersion\Processors;

use Illuminate\Support\Facades\Config;

class DeprecationProcessor
{
    /**
     * Check if a version is deprecated.
     */
    public static function isDeprecated(string $version): bool
    {
        /** @var array<string, array<string, mixed>> $deprecatedVersions */
        $deprecatedVersions = Config::get('api-version.deprecated_versions', []);

        return isset($deprecatedVersions[$version]);
    }

    /**
     * Get deprecation information for a version.
     *
     * @return array<string, mixed>|null
     */
    public static function getDeprecationInfo(string $version): ?array
    {
        /** @var array<string, array<string, mixed>> $deprecatedVersions */
        $deprecatedVersions = Config::get('api-version.deprecated_versions', []);

        return $deprecatedVersions[$version] ?? null;
    }

    /**
     * Get deprecation headers for a deprecated version.
     *
     * @return array<string, string>
     */
    public static function getDeprecationHeaders(string $version): array
    {
        if (! self::isDeprecated($version)) {
            return [];
        }

        $info = self::getDeprecationInfo($version);
        $headers = [
            'Deprecation' => 'true',
        ];

        if (isset($info['sunset_date'])) {
            $headers['Sunset'] = $info['sunset_date'];
        }

        if (isset($info['replacement'])) {
            $headers['Link'] = sprintf('<%s>; rel="successor-version"',
                self::buildReplacementUrl($info['replacement'])
            );
        }

        if (isset($info['message'])) {
            $headers['X-API-Deprecation-Message'] = $info['message'];
        }

        return $headers;
    }

    /**
     * Build replacement URL for the successor version.
     */
    protected static function buildReplacementUrl(string $replacementVersion): string
    {
        // For now, just return a placeholder URL
        // In a real application, this would build the actual API URL
        return config('app.url', 'https://api.example.com').'/'.$replacementVersion;
    }

    /**
     * Validate deprecation configuration.
     *
     * @param  array<string, array<string, mixed>>  $deprecatedVersions
     */
    public static function validateDeprecationConfig(array $deprecatedVersions): void
    {
        foreach ($deprecatedVersions as $version => $config) {
            if (! is_string($version) || ! preg_match('/^v\d+(\.\d+)*$/', $version)) {
                throw new \InvalidArgumentException(
                    "Invalid deprecated version format: {$version}. Expected format: v1, v2, etc."
                );
            }

            if (! is_array($config)) {
                throw new \InvalidArgumentException(
                    "Deprecation config for version {$version} must be an array."
                );
            }

            // Validate sunset_date if provided
            if (isset($config['sunset_date'])) {
                if (! is_string($config['sunset_date']) ||
                    ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $config['sunset_date'])) {
                    throw new \InvalidArgumentException(
                        "Invalid sunset_date format for version {$version}. Expected YYYY-MM-DD format."
                    );
                }
            }

            // Validate replacement if provided
            if (isset($config['replacement'])) {
                if (! is_string($config['replacement']) ||
                    ! preg_match('/^v\d+(\.\d+)*$/', $config['replacement'])) {
                    throw new \InvalidArgumentException(
                        "Invalid replacement version format for {$version}. Expected format: v1, v2, etc."
                    );
                }
            }

            // Validate message if provided
            if (isset($config['message']) && ! is_string($config['message'])) {
                throw new \InvalidArgumentException(
                    "Deprecation message for version {$version} must be a string."
                );
            }
        }
    }
}
