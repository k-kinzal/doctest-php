<?php

/**
 * Example representation for doctest-php.
 *
 * @example File-level example: example with code
 *     $target = new \Doctest\Scanner\Target(
 *         type: \Doctest\Scanner\TargetType::Function_,
 *         filePath: '/app/helpers.php',
 *         docblock: 'fn docs',
 *         name: 'helper',
 *         line: 1,
 *     );
 *     $example = new \Doctest\Parser\Example('1 + 1', $target, 5, 0);
 *     $example->code // => '1 + 1'
 */

declare(strict_types=1);

namespace Doctest\Parser;

use Doctest\Scanner\Target;

/**
 * Represents a single example extracted from a docblock.
 *
 * An Example contains the code to execute, the target it belongs to,
 * and metadata about its location and description.
 *
 * @example Creating an example and getting its name
 *     $target = new \Doctest\Scanner\Target(
 *         type: \Doctest\Scanner\TargetType::Method,
 *         filePath: '/path/to/File.php',
 *         docblock: 'some docs',
 *         name: 'add',
 *         line: 10,
 *         className: 'Calculator',
 *     );
 *     $example = new \Doctest\Parser\Example(
 *         code: '$calc->add(1, 2)',
 *         target: $target,
 *         lineNumber: 15,
 *         index: 0,
 *         description: 'Adding two numbers',
 *     );
 *     $example->getName() // => 'Calculator::add() example #1: Adding two numbers'
 *
 * @example Getting test name
 *     $target = new \Doctest\Scanner\Target(
 *         type: \Doctest\Scanner\TargetType::Class_,
 *         filePath: '/path/to/File.php',
 *         docblock: 'some docs',
 *         name: 'Calculator',
 *         line: 5,
 *     );
 *     $example = new \Doctest\Parser\Example(
 *         code: 'new Calculator()',
 *         target: $target,
 *         lineNumber: 8,
 *         index: 0,
 *     );
 *     $example->getTestName() // => 'Doctest: Calculator example #1 (line 8)'
 */
final class Example
{
    /**
     * @param string $code The example code to execute
     * @param Target $target The target this example belongs to
     * @param int $lineNumber Line number in the source file
     * @param int $index Zero-based index among examples for this target
     * @param string|null $description Optional description from @example tag
     */
    public function __construct(
        public readonly string $code,
        public readonly Target $target,
        public readonly int $lineNumber,
        public readonly int $index,
        public readonly ?string $description = null,
    ) {}

    /**
     * Returns a human-readable name for the example.
     *
     * Combines the target's short name with the example index and optional description.
     */
    public function getName(): string
    {
        $name = $this->target->getShortName();
        $desc = $this->description !== null ? ': ' . $this->description : '';

        return sprintf('%s example #%d%s', $name, $this->index + 1, $desc);
    }

    /**
     * Returns the PHPUnit test name for this example.
     *
     * Includes the example name and line number for identification.
     */
    public function getTestName(): string
    {
        return sprintf(
            'Doctest: %s (line %d)',
            $this->getName(),
            $this->lineNumber,
        );
    }
}
