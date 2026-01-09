<?php

declare(strict_types=1);

namespace Doctest\Tests\Fixtures;

/**
 * A greeter class demonstrating output assertions.
 *
 * ```php
 * $greeter = new \Doctest\Tests\Fixtures\Greeter();
 * $greeter->getName() // => 'World'
 * ```
 */
class Greeter
{
    public function __construct(
        private string $name = 'World',
    ) {}

    /**
     * Get the name.
     *
     * @example
     * $greeter = new \Doctest\Tests\Fixtures\Greeter('Alice');
     * $greeter->getName() // => 'Alice'
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Print a greeting message.
     *
     * @example
     * $greeter = new \Doctest\Tests\Fixtures\Greeter('Bob');
     * $greeter->greet(); // Output: Hello, Bob!
     *
     * @example Default greeting
     * $greeter = new \Doctest\Tests\Fixtures\Greeter();
     * $greeter->greet(); // Output: Hello, World!
     */
    public function greet(): void
    {
        echo "Hello, {$this->name}!";
    }

    /**
     * Get a greeting message as a string.
     *
     * ```php
     * $greeter = new \Doctest\Tests\Fixtures\Greeter('Charlie');
     * $greeter->getGreeting() // => 'Hello, Charlie!'
     * ```
     */
    public function getGreeting(): string
    {
        return "Hello, {$this->name}!";
    }
}
