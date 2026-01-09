<?php

declare(strict_types=1);

namespace Doctest\Tests\Fixtures;

/**
 * Add two numbers together.
 *
 * @example
 * \Doctest\Tests\Fixtures\add(1, 2) // => 3
 * \Doctest\Tests\Fixtures\add(-5, 5) // => 0
 */
function add(int $a, int $b): int
{
    return $a + $b;
}

/**
 * Check if a number is even.
 *
 * ```php
 * \Doctest\Tests\Fixtures\isEven(2) // => true
 * \Doctest\Tests\Fixtures\isEven(3) // => false
 * \Doctest\Tests\Fixtures\isEven(0) // => true
 * ```
 */
function isEven(int $n): bool
{
    return $n % 2 === 0;
}

/**
 * Format a name.
 *
 * @example Simple smoke test (no assertion)
 * $name = \Doctest\Tests\Fixtures\formatName('john', 'doe');
 * echo $name;
 *
 * @example With assertion
 * \Doctest\Tests\Fixtures\formatName('john', 'doe') // => 'John Doe'
 */
function formatName(string $first, string $last): string
{
    return ucfirst($first) . ' ' . ucfirst($last);
}
