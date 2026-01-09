<?php

declare(strict_types=1);

namespace Doctest\Assertion;

/**
 * Represents an assertion to verify in a doctest example.
 *
 * An assertion specifies what should be checked after executing code:
 * - ReturnValue: Check that the expression returns a specific value
 * - Output: Check that the code produces specific output
 * - Exception: Check that the code throws a specific exception
 *
 * @example Creating a return value assertion
 *     $assertion = new \Doctest\Assertion\Assertion(
 *         type: \Doctest\Assertion\AssertionType::ReturnValue,
 *         expectedRaw: '42',
 *     );
 *     $assertion->type->value // => 'return'
 *     $assertion->expectedRaw // => '42'
 *
 * @example Creating an exception assertion with message
 *     $assertion = new \Doctest\Assertion\Assertion(
 *         type: \Doctest\Assertion\AssertionType::Exception,
 *         expectedRaw: 'InvalidArgumentException',
 *         exceptionMessage: 'Value must be positive',
 *     );
 *     $assertion->exceptionMessage // => 'Value must be positive'
 */
final class Assertion
{
    /**
     * @param AssertionType $type The type of assertion
     * @param string $expectedRaw The raw expected value or exception class name
     * @param string|null $exceptionMessage Expected exception message (for Exception type)
     */
    public function __construct(
        public readonly AssertionType $type,
        public readonly string $expectedRaw,
        public readonly ?string $exceptionMessage = null,
    ) {}
}
