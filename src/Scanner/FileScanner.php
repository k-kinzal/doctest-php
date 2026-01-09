<?php

declare(strict_types=1);

namespace Doctest\Scanner;

use Doctest\Configuration\Configuration;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;

/**
 * Scans directories and files for PHP source files based on configuration.
 *
 * FileScanner iterates through configured directories and files, filtering
 * out excluded patterns, to produce a list of PHP files that should be
 * processed for doctest examples.
 *
 * @example Scanning current directory
 *     $config = new \Doctest\Configuration\Configuration(directories: [__DIR__]);
 *     $scanner = new \Doctest\Scanner\FileScanner($config);
 *     $files = iterator_to_array($scanner->scan());
 *     is_array($files) // => true
 */
final class FileScanner
{
    /**
     * @param Configuration $config The configuration specifying directories and exclusions
     */
    public function __construct(
        private readonly Configuration $config,
    ) {}

    /**
     * Scans all configured sources and yields PHP file paths.
     *
     * Iterates through configured directories recursively, as well as
     * individual files, filtering based on exclude patterns.
     *
     * @return iterable<string> Absolute paths to PHP files
     */
    public function scan(): iterable
    {
        foreach ($this->config->getDirectories() as $directory) {
            if (!is_dir($directory)) {
                continue;
            }
            yield from $this->scanDirectory($directory);
        }

        foreach ($this->config->getFiles() as $file) {
            if (is_file($file) && $this->shouldInclude($file)) {
                yield $file;
            }
        }
    }

    /**
     * @return iterable<string>
     */
    private function scanDirectory(string $directory): iterable
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        /** @var RegexIterator<int, SplFileInfo, RecursiveIteratorIterator<RecursiveDirectoryIterator>> $phpFiles */
        $phpFiles = new RegexIterator($iterator, '/\.php$/');

        foreach ($phpFiles as $file) {
            $path = $file->getPathname();
            if ($this->shouldInclude($path)) {
                yield $path;
            }
        }
    }

    private function shouldInclude(string $path): bool
    {
        foreach ($this->config->getExcludePatterns() as $pattern) {
            if (fnmatch($pattern, $path) || fnmatch('*' . $pattern, $path)) {
                return false;
            }
        }

        return true;
    }
}
