<?php

/**
 * Configuration module for doctest-php.
 *
 * @example File-level example: creating config with defaults
 *     $config = new \Doctest\Configuration\Configuration();
 *     $config->isEnabled() // => true
 *     $config->getDirectories() // => []
 */

declare(strict_types=1);

namespace Doctest\Configuration;

/**
 * Holds configuration settings for doctest execution.
 *
 * Configuration specifies which directories and files to scan for doctest
 * examples, patterns to exclude, and optional bootstrap file.
 *
 * @example Creating a basic configuration
 *     $config = new \Doctest\Configuration\Configuration(
 *         directories: ['/app/src'],
 *     );
 *     $config->getDirectories() // => ['/app/src']
 *     $config->isEnabled() // => true
 *
 * @example Configuration with exclusions
 *     $config = new \Doctest\Configuration\Configuration(
 *         directories: ['/app/src'],
 *         excludePatterns: ['*Test.php', '*Interface.php'],
 *     );
 *     count($config->getExcludePatterns()) // => 2
 *
 * @example Configuration with bootstrap
 *     $config = new \Doctest\Configuration\Configuration(
 *         directories: ['/app/src'],
 *         bootstrap: '/app/vendor/autoload.php',
 *     );
 *     $config->getBootstrap() // => '/app/vendor/autoload.php'
 */
final class Configuration
{
    /**
     * @param list<string> $directories Directories to scan for PHP files
     * @param list<string> $files Individual files to scan
     * @param list<string> $excludePatterns Glob patterns to exclude
     * @param string|null $bootstrap Path to bootstrap file to require before tests
     * @param bool $enabled Whether doctest is enabled
     */
    public function __construct(
        private readonly array $directories = [],
        private readonly array $files = [],
        private readonly array $excludePatterns = [],
        private readonly ?string $bootstrap = null,
        private readonly bool $enabled = true,
    ) {}

    /**
     * Returns the list of directories to scan.
     *
     * @return list<string>
     *
     * @example Getting directories
     *     $config = new \Doctest\Configuration\Configuration(directories: ['/src', '/lib']);
     *     $config->getDirectories() // => ['/src', '/lib']
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }

    /**
     * Returns the list of individual files to scan.
     *
     * @return list<string>
     *
     * @example Getting files
     *     $config = new \Doctest\Configuration\Configuration(files: ['/app/helpers.php']);
     *     $config->getFiles() // => ['/app/helpers.php']
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Returns the list of exclude patterns.
     *
     * @return list<string>
     *
     * @example Getting exclude patterns
     *     $config = new \Doctest\Configuration\Configuration(excludePatterns: ['*Test.php']);
     *     $config->getExcludePatterns() // => ['*Test.php']
     */
    public function getExcludePatterns(): array
    {
        return $this->excludePatterns;
    }

    /**
     * Returns the path to the bootstrap file.
     *
     * @example Getting bootstrap when set
     *     $config = new \Doctest\Configuration\Configuration(bootstrap: '/app/bootstrap.php');
     *     $config->getBootstrap() // => '/app/bootstrap.php'
     *
     * @example Getting bootstrap when not set
     *     $config = new \Doctest\Configuration\Configuration();
     *     $config->getBootstrap() // => null
     */
    public function getBootstrap(): ?string
    {
        return $this->bootstrap;
    }

    /**
     * Returns whether doctest is enabled.
     *
     * @example Checking enabled state
     *     $config = new \Doctest\Configuration\Configuration(enabled: false);
     *     $config->isEnabled() // => false
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
