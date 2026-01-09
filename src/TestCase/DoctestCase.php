<?php

declare(strict_types=1);

namespace Doctest\TestCase;

use Doctest\Executor\ExampleExecutor;
use Doctest\Parser\Example;
use PHPUnit\Framework\TestCase;

/**
 * PHPUnit test case for a single doctest example.
 *
 * DoctestCase wraps an Example and executes it as a PHPUnit test.
 * It uses a shared ExampleExecutor instance for efficiency.
 *
 * @example Creating a test case
 *     $target = new \Doctest\Scanner\Target(
 *         type: \Doctest\Scanner\TargetType::Class_,
 *         filePath: __FILE__,
 *         docblock: 'docs',
 *         name: 'Test',
 *         line: 1,
 *     );
 *     $example = new \Doctest\Parser\Example('true', $target, 1, 0);
 *     $testCase = new \Doctest\TestCase\DoctestCase($example);
 *     $testCase->getName() // => 'Doctest: Test example #1 (line 1)'
 */
final class DoctestCase extends TestCase
{
    private static ?ExampleExecutor $executor = null;

    /**
     * @param Example $example The example to test
     * @param string|null $bootstrap Path to bootstrap file
     */
    public function __construct(
        private readonly Example $example,
        ?string $bootstrap = null,
    ) {
        $testName = $this->example->getTestName();
        assert($testName !== '');
        parent::__construct($testName);

        if (self::$executor === null) {
            self::$executor = new ExampleExecutor($bootstrap);
        }
    }

    /**
     * Returns the test name.
     *
     * @param bool $withDataSet Whether to include data set info (unused)
     */
    public function getName(bool $withDataSet = true): string
    {
        return $this->example->getTestName();
    }

    /**
     * Returns a string representation of the test.
     */
    public function toString(): string
    {
        return $this->example->getTestName();
    }

    /**
     * Runs the doctest example.
     */
    protected function runTest(): void
    {
        assert(self::$executor !== null);
        $result = self::$executor->execute($this->example);

        if (!$result->passed) {
            $this->fail($result->getErrorMessage());
        }

        $this->assertTrue(true);
    }
}
