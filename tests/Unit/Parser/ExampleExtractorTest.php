<?php

declare(strict_types=1);

namespace Doctest\Tests\Unit\Parser;

use Doctest\Parser\ExampleExtractor;
use Doctest\Scanner\Target;
use Doctest\Scanner\TargetType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ExampleExtractorTest extends TestCase
{
    private ExampleExtractor $extractor;

    protected function setUp(): void
    {
        $this->extractor = new ExampleExtractor();
    }

    #[Test]
    public function extractsExampleFromExampleTag(): void
    {
        $docblock = <<<'DOC'
            /**
             * Some description.
             *
             * @example
             * $result = add(1, 2);
             * $result // => 3
             */
            DOC;

        $target = new Target(
            type: TargetType::Function_,
            filePath: '/test.php',
            docblock: $docblock,
            name: 'add',
            line: 10,
        );

        $examples = iterator_to_array($this->extractor->extract($target));

        $this->assertCount(1, $examples);
        $this->assertStringContainsString('$result = add(1, 2)', $examples[0]->code);
        $this->assertStringContainsString('$result // => 3', $examples[0]->code);
    }

    #[Test]
    public function extractsExampleFromCodeFence(): void
    {
        $docblock = <<<'DOC'
            /**
             * Some description.
             *
             * ```php
             * $x = 5;
             * $x * 2 // => 10
             * ```
             */
            DOC;

        $target = new Target(
            type: TargetType::Method,
            filePath: '/test.php',
            docblock: $docblock,
            name: 'multiply',
            line: 20,
            className: 'Calculator',
        );

        $examples = iterator_to_array($this->extractor->extract($target));

        $this->assertCount(1, $examples);
        $this->assertStringContainsString('$x = 5', $examples[0]->code);
        $this->assertStringContainsString('$x * 2 // => 10', $examples[0]->code);
    }

    #[Test]
    public function extractsMultipleExamples(): void
    {
        $docblock = <<<'DOC'
            /**
             * @example First example
             * add(1, 1) // => 2
             *
             * @example Second example
             * add(2, 2) // => 4
             */
            DOC;

        $target = new Target(
            type: TargetType::Function_,
            filePath: '/test.php',
            docblock: $docblock,
            name: 'add',
            line: 1,
        );

        $examples = iterator_to_array($this->extractor->extract($target));

        $this->assertCount(2, $examples);
        $this->assertEquals('First example', $examples[0]->description);
        $this->assertEquals('Second example', $examples[1]->description);
    }

    #[Test]
    public function handlesEmptyDocblock(): void
    {
        $docblock = <<<'DOC'
            /**
             * Just a description without examples.
             */
            DOC;

        $target = new Target(
            type: TargetType::Function_,
            filePath: '/test.php',
            docblock: $docblock,
            name: 'test',
            line: 1,
        );

        $examples = iterator_to_array($this->extractor->extract($target));

        $this->assertCount(0, $examples);
    }
}
