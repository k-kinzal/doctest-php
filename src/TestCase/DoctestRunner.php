<?php

declare(strict_types=1);

namespace Doctest\TestCase;

use Doctest\Configuration\Configuration;
use Doctest\Executor\ExampleExecutor;
use Doctest\Parser\Example;
use Doctest\Parser\ExampleExtractor;
use Doctest\Scanner\FileScanner;
use Doctest\Scanner\SourceScanner;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Base class for running doctests as PHPUnit tests.
 *
 * Extend this class and implement the configure() method to run doctests
 * for your project.
 *
 * @example Checking class hierarchy
 *     is_a(\Doctest\TestCase\DoctestRunner::class, \PHPUnit\Framework\TestCase::class, true) // => true
 */
abstract class DoctestRunner extends TestCase
{
    private static ?ExampleExecutor $executor = null;

    /**
     * Override to provide custom configuration.
     *
     * @return Configuration The configuration for doctest scanning
     */
    abstract protected static function configure(): Configuration;

    /**
     * Provides examples as test data.
     *
     * @return iterable<string, array{Example}>
     */
    public static function doctestProvider(): iterable
    {
        $config = static::configure();

        $fileScanner = new FileScanner($config);
        $sourceScanner = new SourceScanner();
        $extractor = new ExampleExtractor();

        foreach ($fileScanner->scan() as $filePath) {
            foreach ($sourceScanner->scanFile($filePath) as $target) {
                foreach ($extractor->extract($target) as $example) {
                    yield $example->getName() => [$example];
                }
            }
        }
    }

    /**
     * Tests a docblock example.
     *
     * @param Example $example The example to test
     */
    #[Test]
    #[DataProvider('doctestProvider')]
    public function testDocblockExample(Example $example): void
    {
        $config = static::configure();

        if (self::$executor === null) {
            self::$executor = new ExampleExecutor($config->getBootstrap());
        }

        $result = self::$executor->execute($example);

        if (!$result->passed) {
            $this->fail($result->getErrorMessage());
        }

        $this->assertTrue(true);
    }
}
