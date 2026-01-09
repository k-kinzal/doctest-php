<?php

/**
 * PHPUnit extension entry point for doctest-php.
 *
 * @example File-level example: checking extension class
 *     class_exists(\Doctest\DoctestExtension::class) // => true
 */

declare(strict_types=1);

namespace Doctest;

use Doctest\Configuration\Configuration;
use Doctest\Configuration\ConfigurationLoader;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration as PHPUnitConfiguration;

/**
 * PHPUnit Extension for doctest-php.
 *
 * This extension loads doctest configuration from phpunit.xml parameters
 * and makes it available to DoctestSuite.
 *
 * @example Checking extension interface
 *     $implements = class_implements(\Doctest\DoctestExtension::class);
 *     in_array(\PHPUnit\Runner\Extension\Extension::class, $implements, true) // => true
 */
final class DoctestExtension implements Extension
{
    private static ?Configuration $configuration = null;

    /**
     * Bootstraps the extension.
     *
     * Called by PHPUnit when the extension is loaded.
     *
     * @param PHPUnitConfiguration $configuration PHPUnit configuration
     * @param Facade $facade PHPUnit extension facade
     * @param ParameterCollection $parameters Extension parameters from phpunit.xml
     */
    public function bootstrap(
        PHPUnitConfiguration $configuration,
        Facade $facade,
        ParameterCollection $parameters,
    ): void {
        // Determine base path from PHPUnit config file location
        $configFile = $configuration->configurationFile();
        $basePath = $configFile !== '' ? dirname($configFile) : (getcwd() ?: '');

        // Load doctest configuration from extension parameters
        $config = ConfigurationLoader::fromParameters($parameters, $basePath);

        if (!$config->isEnabled()) {
            return;
        }

        // Store configuration for later access by DoctestRunner
        self::$configuration = $config;
    }

    /**
     * Returns the stored configuration.
     *
     * @return Configuration|null The configuration, or null if not initialized
     */
    public static function getConfiguration(): ?Configuration
    {
        return self::$configuration;
    }
}
