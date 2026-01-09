<?php

declare(strict_types=1);

namespace Doctest\Assertion;

/**
 * Represents the result of evaluating an assertion.
 *
 * Contains information about whether the assertion passed, along with
 * details for generating meaningful error messages on failure.
 *
 * @example Creating a passing result
 *     $statement = new \Doctest\Assertion\Statement('1 + 1', null, 1);
 *     $result = new \Doctest\Assertion\AssertionResult(
 *         passed: true,
 *         message: '',
 *         statement: $statement,
 *     );
 *     $result->passed // => true
 *     $result->getDetailedMessage() // => 'OK'
 *
 * @example Creating a failing result
 *     $assertion = new \Doctest\Assertion\Assertion(\Doctest\Assertion\AssertionType::ReturnValue, '3');
 *     $statement = new \Doctest\Assertion\Statement('1 + 1', $assertion, 1);
 *     $result = new \Doctest\Assertion\AssertionResult(
 *         passed: false,
 *         message: 'Values do not match',
 *         statement: $statement,
 *         expected: 3,
 *         actual: 2,
 *     );
 *     $result->passed // => false
 */
final class AssertionResult
{
    /**
     * @param bool $passed Whether the assertion passed
     * @param string $message Error message if failed
     * @param Statement $statement The statement that was evaluated
     * @param mixed $expected The expected value, if applicable
     * @param mixed $actual The actual value, if applicable
     */
    public function __construct(
        public readonly bool $passed,
        public readonly string $message,
        public readonly Statement $statement,
        public readonly mixed $expected = null,
        public readonly mixed $actual = null,
    ) {}

    /**
     * Returns a detailed message describing the result.
     *
     * For passing assertions, returns 'OK'.
     * For failing assertions, includes the code, expected value,
     * actual value, and error message.
     */
    public function getDetailedMessage(): string
    {
        if ($this->passed) {
            return 'OK';
        }

        $parts = [
            'Assertion failed',
            'Code: ' . $this->statement->code,
        ];

        if ($this->expected !== null) {
            $parts[] = 'Expected: ' . $this->formatValue($this->expected);
        }

        if ($this->actual !== null) {
            $parts[] = 'Actual: ' . $this->formatValue($this->actual);
        }

        if ($this->message !== '') {
            $parts[] = 'Message: ' . $this->message;
        }

        return implode("\n  ", $parts);
    }

    private function formatValue(mixed $value): string
    {
        if (is_string($value)) {
            return '"' . $value . '"';
        }

        if (is_array($value)) {
            return var_export($value, true);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_object($value)) {
            return get_class($value);
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return var_export($value, true);
    }
}
