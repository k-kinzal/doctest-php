<?php

declare(strict_types=1);

namespace Doctest\Assertion;

/**
 * Represents a single statement within an example, with optional assertion.
 *
 * A Statement contains a piece of code and optionally an assertion about
 * what that code should produce. Statements without assertions serve as
 * "smoke tests" - they just verify the code runs without errors.
 *
 * @example Statement with assertion
 *     $assertion = new \Doctest\Assertion\Assertion(
 *         type: \Doctest\Assertion\AssertionType::ReturnValue,
 *         expectedRaw: '3',
 *     );
 *     $statement = new \Doctest\Assertion\Statement(
 *         code: '1 + 2',
 *         assertion: $assertion,
 *         line: 5,
 *     );
 *     $statement->hasAssertion() // => true
 *
 * @example Statement without assertion (smoke test)
 *     $statement = new \Doctest\Assertion\Statement(
 *         code: '$x = 1;',
 *         assertion: null,
 *         line: 10,
 *     );
 *     $statement->hasAssertion() // => false
 */
final class Statement
{
    /**
     * @param string $code The code to execute
     * @param Assertion|null $assertion The assertion to verify, or null for smoke test
     * @param int $line Line number within the example
     */
    public function __construct(
        public readonly string $code,
        public readonly ?Assertion $assertion,
        public readonly int $line,
    ) {}

    /**
     * Returns whether this statement has an assertion.
     *
     * Statements without assertions are "smoke tests" - they just verify
     * that the code executes without throwing exceptions.
     */
    public function hasAssertion(): bool
    {
        return $this->assertion !== null;
    }
}
