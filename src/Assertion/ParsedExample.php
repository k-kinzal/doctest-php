<?php

declare(strict_types=1);

namespace Doctest\Assertion;

use Doctest\Parser\Example;

/**
 * Represents an example that has been parsed into individual statements.
 *
 * A ParsedExample contains the original Example along with a list of
 * Statement objects extracted from the example code.
 */
final class ParsedExample
{
    /**
     * @param Example $example The original example
     * @param list<Statement> $statements The parsed statements
     */
    public function __construct(
        public readonly Example $example,
        public readonly array $statements,
    ) {}
}
