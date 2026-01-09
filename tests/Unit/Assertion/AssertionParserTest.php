<?php

declare(strict_types=1);

namespace Doctest\Tests\Unit\Assertion;

use Doctest\Assertion\AssertionParser;
use Doctest\Assertion\AssertionType;
use Doctest\Parser\Example;
use Doctest\Scanner\Target;
use Doctest\Scanner\TargetType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AssertionParserTest extends TestCase
{
    private AssertionParser $parser;

    protected function setUp(): void
    {
        $this->parser = new AssertionParser();
    }

    private function createExample(string $code): Example
    {
        $target = new Target(
            type: TargetType::Function_,
            filePath: '/test.php',
            docblock: '/** */',
            name: 'test',
            line: 1,
        );

        return new Example(
            code: $code,
            target: $target,
            lineNumber: 1,
            index: 0,
        );
    }

    #[Test]
    public function parsesReturnValueAssertion(): void
    {
        $example = $this->createExample('add(1, 2) // => 3');
        $parsed = $this->parser->parse($example);

        $this->assertCount(1, $parsed->statements);
        $statement = $parsed->statements[0];

        $this->assertEquals('add(1, 2)', $statement->code);
        $this->assertNotNull($statement->assertion);
        $this->assertEquals(AssertionType::ReturnValue, $statement->assertion->type);
        $this->assertEquals('3', $statement->assertion->expectedRaw);
    }

    #[Test]
    public function parsesOutputAssertion(): void
    {
        $example = $this->createExample('echo "hello"; // Output: hello');
        $parsed = $this->parser->parse($example);

        $this->assertCount(1, $parsed->statements);
        $statement = $parsed->statements[0];

        $this->assertEquals('echo "hello";', $statement->code);
        $this->assertNotNull($statement->assertion);
        $this->assertEquals(AssertionType::Output, $statement->assertion->type);
        $this->assertEquals('hello', $statement->assertion->expectedRaw);
    }

    #[Test]
    public function parsesExceptionAssertion(): void
    {
        $example = $this->createExample('throw new Exception(); // throws Exception');
        $parsed = $this->parser->parse($example);

        $this->assertCount(1, $parsed->statements);
        $statement = $parsed->statements[0];

        $this->assertEquals('throw new Exception();', $statement->code);
        $this->assertNotNull($statement->assertion);
        $this->assertEquals(AssertionType::Exception, $statement->assertion->type);
        $this->assertEquals('Exception', $statement->assertion->expectedRaw);
    }

    #[Test]
    public function parsesExceptionAssertionWithMessage(): void
    {
        $example = $this->createExample('throw new Exception("error"); // throws Exception: error');
        $parsed = $this->parser->parse($example);

        $this->assertCount(1, $parsed->statements);
        $statement = $parsed->statements[0];

        $this->assertNotNull($statement->assertion);
        $this->assertEquals(AssertionType::Exception, $statement->assertion->type);
        $this->assertEquals('Exception', $statement->assertion->expectedRaw);
        $this->assertEquals('error', $statement->assertion->exceptionMessage);
    }

    #[Test]
    public function parsesStatementWithoutAssertion(): void
    {
        $example = $this->createExample('$x = 5;');
        $parsed = $this->parser->parse($example);

        $this->assertCount(1, $parsed->statements);
        $statement = $parsed->statements[0];

        $this->assertEquals('$x = 5;', $statement->code);
        $this->assertNull($statement->assertion);
    }

    #[Test]
    public function parsesMultilineExample(): void
    {
        $code = <<<'CODE'
            $a = 1;
            $b = 2;
            $a + $b // => 3
            CODE;

        $example = $this->createExample($code);
        $parsed = $this->parser->parse($example);

        $this->assertCount(3, $parsed->statements);
        $this->assertNull($parsed->statements[0]->assertion);
        $this->assertNull($parsed->statements[1]->assertion);
        $this->assertNotNull($parsed->statements[2]->assertion);
        $this->assertEquals(AssertionType::ReturnValue, $parsed->statements[2]->assertion->type);
    }

    #[Test]
    public function parsesArrayExpectedValue(): void
    {
        $example = $this->createExample("getItems() // => ['a', 'b', 'c']");
        $parsed = $this->parser->parse($example);

        $this->assertCount(1, $parsed->statements);
        $this->assertEquals("['a', 'b', 'c']", $parsed->statements[0]->assertion->expectedRaw);
    }
}
