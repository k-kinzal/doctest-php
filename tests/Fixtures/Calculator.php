<?php

declare(strict_types=1);

namespace Doctest\Tests\Fixtures;

/**
 * A simple calculator for testing.
 *
 * @example
 * $calc = new \Doctest\Tests\Fixtures\Calculator();
 * $calc->add(1, 2) // => 3
 */
class Calculator
{
    private int $lastResult = 0;

    /**
     * Add two numbers.
     *
     * ```php
     * $calc = new \Doctest\Tests\Fixtures\Calculator();
     * $calc->add(5, 3) // => 8
     * $calc->add(-1, 1) // => 0
     * $calc->add(0, 0) // => 0
     * ```
     */
    public function add(int $a, int $b): int
    {
        $this->lastResult = $a + $b;
        return $this->lastResult;
    }

    /**
     * Subtract two numbers.
     *
     * @example
     * $calc = new \Doctest\Tests\Fixtures\Calculator();
     * $calc->subtract(10, 3) // => 7
     * $calc->subtract(5, 5) // => 0
     */
    public function subtract(int $a, int $b): int
    {
        $this->lastResult = $a - $b;
        return $this->lastResult;
    }

    /**
     * Multiply two numbers.
     *
     * @example Basic multiplication
     * $calc = new \Doctest\Tests\Fixtures\Calculator();
     * $calc->multiply(3, 4) // => 12
     *
     * @example Multiplication with zero
     * $calc = new \Doctest\Tests\Fixtures\Calculator();
     * $calc->multiply(5, 0) // => 0
     */
    public function multiply(int $a, int $b): int
    {
        $this->lastResult = $a * $b;
        return $this->lastResult;
    }

    /**
     * Divide two numbers.
     *
     * ```php
     * $calc = new \Doctest\Tests\Fixtures\Calculator();
     * $calc->divide(10, 2) // => 5
     * $calc->divide(9, 3) // => 3
     * ```
     *
     * @example Division by zero throws exception
     * $calc = new \Doctest\Tests\Fixtures\Calculator();
     * $calc->divide(10, 0) // throws \DivisionByZeroError
     */
    public function divide(int $a, int $b): int
    {
        if ($b === 0) {
            throw new \DivisionByZeroError('Cannot divide by zero');
        }
        $this->lastResult = intdiv($a, $b);
        return $this->lastResult;
    }

    /**
     * Get the last calculation result.
     *
     * @example
     * $calc = new \Doctest\Tests\Fixtures\Calculator();
     * $calc->add(5, 3);
     * $calc->getLastResult() // => 8
     */
    public function getLastResult(): int
    {
        return $this->lastResult;
    }
}
