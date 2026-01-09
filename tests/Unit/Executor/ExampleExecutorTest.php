<?php

declare(strict_types=1);

namespace Doctest\Tests\Unit\Executor;

use Doctest\Executor\ExampleExecutor;
use Doctest\Parser\Example;
use Doctest\Scanner\Target;
use Doctest\Scanner\TargetType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ExampleExecutorTest extends TestCase
{
    private ExampleExecutor $executor;

    protected function setUp(): void
    {
        $this->executor = new ExampleExecutor();
    }

    private function createExample(string $code, string $filePath = __DIR__ . '/../../Fixtures/Calculator.php'): Example
    {
        $target = new Target(
            type: TargetType::Method,
            filePath: $filePath,
            docblock: '/** */',
            name: 'test',
            line: 1,
            className: 'TestClass',
        );

        return new Example(
            code: $code,
            target: $target,
            lineNumber: 1,
            index: 0,
        );
    }

    #[Test]
    public function executesSimpleReturnValueAssertion(): void
    {
        $example = $this->createExample('1 + 2 // => 3');
        $result = $this->executor->execute($example);

        $this->assertTrue($result->passed);
        $this->assertEmpty($result->failures);
    }

    #[Test]
    public function failsOnIncorrectReturnValue(): void
    {
        $example = $this->createExample('1 + 2 // => 4');
        $result = $this->executor->execute($example);

        $this->assertFalse($result->passed);
        $this->assertNotEmpty($result->failures);
    }

    #[Test]
    public function executesOutputAssertion(): void
    {
        $example = $this->createExample('echo "hello"; // Output: hello');
        $result = $this->executor->execute($example);

        $this->assertTrue($result->passed);
    }

    #[Test]
    public function executesStatementWithoutAssertion(): void
    {
        $example = $this->createExample('$x = 5;');
        $result = $this->executor->execute($example);

        $this->assertTrue($result->passed, 'Smoke test should pass when no exception is thrown');
    }

    #[Test]
    public function failsOnUnexpectedException(): void
    {
        $example = $this->createExample('throw new \RuntimeException("error");');
        $result = $this->executor->execute($example);

        $this->assertFalse($result->passed);
        $this->assertStringContainsString('RuntimeException', $result->getErrorMessage());
    }

    #[Test]
    public function passesOnExpectedException(): void
    {
        $example = $this->createExample('throw new \RuntimeException("error"); // throws RuntimeException');
        $result = $this->executor->execute($example);

        $this->assertTrue($result->passed);
    }

    #[Test]
    public function failsWhenExpectedExceptionNotThrown(): void
    {
        $example = $this->createExample('$x = 5; // throws RuntimeException');
        $result = $this->executor->execute($example);

        $this->assertFalse($result->passed);
        $this->assertStringContainsString('Expected exception', $result->getErrorMessage());
    }

    #[Test]
    public function maintainsVariableStateBetweenStatements(): void
    {
        $code = <<<'CODE'
            $a = 5;
            $b = 3;
            $a + $b // => 8
            CODE;

        $example = $this->createExample($code);
        $result = $this->executor->execute($example);

        $this->assertTrue($result->passed);
    }

    #[Test]
    public function handlesComplexExpectedValues(): void
    {
        $example = $this->createExample("['a', 'b'] // => ['a', 'b']");
        $result = $this->executor->execute($example);

        $this->assertTrue($result->passed);
    }

    #[Test]
    public function handlesBooleanExpectedValues(): void
    {
        $example = $this->createExample('true // => true');
        $result = $this->executor->execute($example);

        $this->assertTrue($result->passed);
    }

    #[Test]
    public function handlesNullExpectedValue(): void
    {
        $example = $this->createExample('null // => null');
        $result = $this->executor->execute($example);

        $this->assertTrue($result->passed);
    }
}
