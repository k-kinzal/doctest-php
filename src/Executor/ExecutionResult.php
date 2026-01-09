<?php

declare(strict_types=1);

namespace Doctest\Executor;

use Doctest\Assertion\AssertionResult;
use Doctest\Parser\Example;

/**
 * Represents the result of executing an example.
 *
 * Contains the example that was executed, whether it passed all assertions,
 * and a list of any failures that occurred.
 *
 * @example Creating a passing result
 *     $target = new \Doctest\Scanner\Target(
 *         type: \Doctest\Scanner\TargetType::Class_,
 *         filePath: __FILE__,
 *         docblock: 'docs',
 *         name: 'Test',
 *         line: 1,
 *     );
 *     $example = new \Doctest\Parser\Example('1+1', $target, 1, 0);
 *     $result = new \Doctest\Executor\ExecutionResult($example, true);
 *     $result->passed // => true
 *     $result->getErrorMessage() // => ''
 */
final class ExecutionResult
{
    /**
     * @param Example $example The example that was executed
     * @param bool $passed Whether all assertions passed
     * @param list<AssertionResult> $failures List of failed assertions
     */
    public function __construct(
        public readonly Example $example,
        public readonly bool $passed,
        public readonly array $failures = [],
    ) {}

    /**
     * Returns a formatted error message for all failures.
     *
     * Returns an empty string if the example passed.
     */
    public function getErrorMessage(): string
    {
        if ($this->passed) {
            return '';
        }

        $messages = [];
        foreach ($this->failures as $failure) {
            $messages[] = $failure->getDetailedMessage();
        }

        return implode("\n\n", $messages);
    }
}
