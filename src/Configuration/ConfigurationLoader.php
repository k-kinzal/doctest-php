<?php

declare(strict_types=1);

namespace Doctest\Configuration;

use PHPUnit\Runner\Extension\ParameterCollection;

/**
 * Loads Configuration from PHPUnit extension parameters.
 *
 * This loader converts PHPUnit extension parameters into a Configuration object,
 * handling path resolution and parameter parsing.
 *
 * @example Loading configuration from parameters
 *     $params = \PHPUnit\Runner\Extension\ParameterCollection::fromArray([
 *         'directories' => 'src,lib',
 *         'exclude' => '*Test.php',
 *     ]);
 *     $config = \Doctest\Configuration\ConfigurationLoader::fromParameters($params, '/app');
 *     $config->getDirectories() // => ['/app/src', '/app/lib']
 *     $config->getExcludePatterns() // => ['*Test.php']
 */
final class ConfigurationLoader
{
    /**
     * Creates a Configuration from PHPUnit extension parameters.
     *
     * Supported parameters:
     * - directories: Comma-separated list of directories to scan
     * - files: Comma-separated list of files to scan
     * - exclude: Comma-separated list of glob patterns to exclude
     * - bootstrap: Path to bootstrap file
     * - enabled: Boolean to enable/disable doctest
     *
     * @param ParameterCollection $parameters PHPUnit extension parameters
     * @param string $basePath Base path for resolving relative paths
     */
    public static function fromParameters(ParameterCollection $parameters, string $basePath = ''): Configuration
    {
        $directories = [];
        $files = [];
        $excludePatterns = [];
        $bootstrap = null;
        $enabled = true;

        if ($parameters->has('directories')) {
            $dirs = $parameters->get('directories');
            $directories = array_map(
                fn(string $dir): string => self::resolvePath($dir, $basePath),
                array_filter(array_map('trim', explode(',', $dirs)))
            );
        }

        if ($parameters->has('files')) {
            $fileList = $parameters->get('files');
            $files = array_map(
                fn(string $file): string => self::resolvePath($file, $basePath),
                array_filter(array_map('trim', explode(',', $fileList)))
            );
        }

        if ($parameters->has('exclude')) {
            $exclude = $parameters->get('exclude');
            $excludePatterns = array_filter(array_map('trim', explode(',', $exclude)));
        }

        if ($parameters->has('bootstrap')) {
            $bootstrap = self::resolvePath($parameters->get('bootstrap'), $basePath);
        }

        if ($parameters->has('enabled')) {
            $enabled = filter_var($parameters->get('enabled'), FILTER_VALIDATE_BOOLEAN);
        }

        return new Configuration(
            directories: array_values($directories),
            files: array_values($files),
            excludePatterns: array_values($excludePatterns),
            bootstrap: $bootstrap,
            enabled: $enabled,
        );
    }

    private static function resolvePath(string $path, string $basePath): string
    {
        if ($basePath === '' || str_starts_with($path, '/')) {
            return $path;
        }

        return rtrim($basePath, '/') . '/' . ltrim($path, '/');
    }
}
