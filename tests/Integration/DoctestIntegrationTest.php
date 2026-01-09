<?php

declare(strict_types=1);

namespace Doctest\Tests\Integration;

use Doctest\Configuration\Configuration;
use Doctest\Executor\ExampleExecutor;
use Doctest\Parser\ExampleExtractor;
use Doctest\Scanner\FileScanner;
use Doctest\Scanner\SourceScanner;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DoctestIntegrationTest extends TestCase
{
    #[Test]
    public function discoversAndExecutesDoctestsFromFixtures(): void
    {
        $config = new Configuration(
            directories: [__DIR__ . '/../Fixtures'],
        );

        $fileScanner = new FileScanner($config);
        $sourceScanner = new SourceScanner();
        $extractor = new ExampleExtractor();
        $executor = new ExampleExecutor();

        $totalExamples = 0;
        $passedExamples = 0;
        $failedExamples = [];

        foreach ($fileScanner->scan() as $filePath) {
            foreach ($sourceScanner->scanFile($filePath) as $target) {
                foreach ($extractor->extract($target) as $example) {
                    $totalExamples++;
                    $result = $executor->execute($example);

                    if ($result->passed) {
                        $passedExamples++;
                    } else {
                        $failedExamples[] = [
                            'name' => $example->getName(),
                            'error' => $result->getErrorMessage(),
                        ];
                    }
                }
            }
        }

        $this->assertGreaterThan(0, $totalExamples, 'Should discover at least one example');

        if (!empty($failedExamples)) {
            $failureMessages = array_map(
                fn($f) => "{$f['name']}: {$f['error']}",
                $failedExamples
            );
            $this->fail(
                "Some doctests failed:\n" . implode("\n\n", $failureMessages)
            );
        }

        $this->assertEquals($totalExamples, $passedExamples, 'All discovered examples should pass');
    }

    #[Test]
    public function discoversExamplesFromClassDocblock(): void
    {
        $config = new Configuration(
            files: [__DIR__ . '/../Fixtures/Calculator.php'],
        );

        $sourceScanner = new SourceScanner();
        $extractor = new ExampleExtractor();

        $examples = [];
        foreach ($sourceScanner->scanFile(__DIR__ . '/../Fixtures/Calculator.php') as $target) {
            foreach ($extractor->extract($target) as $example) {
                $examples[] = $example;
            }
        }

        $this->assertGreaterThan(5, count($examples), 'Calculator should have multiple examples');

        // Check that we found examples from different methods
        $names = array_map(fn($e) => $e->target->name, $examples);
        $this->assertContains('Calculator', $names, 'Should have class-level example');
        $this->assertContains('add', $names, 'Should have add method example');
        $this->assertContains('divide', $names, 'Should have divide method example');
    }

    #[Test]
    public function discoversExamplesFromFunctions(): void
    {
        $sourceScanner = new SourceScanner();
        $extractor = new ExampleExtractor();

        $examples = [];
        foreach ($sourceScanner->scanFile(__DIR__ . '/../Fixtures/functions.php') as $target) {
            foreach ($extractor->extract($target) as $example) {
                $examples[] = $example;
            }
        }

        $this->assertGreaterThan(0, count($examples), 'functions.php should have examples');

        $names = array_map(fn($e) => $e->target->name, $examples);
        $this->assertContains('add', $names);
        $this->assertContains('isEven', $names);
    }
}
