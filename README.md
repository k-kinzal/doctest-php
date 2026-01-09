# doctest-php

PHPUnit extension for testing examples in PHP docblocks.

Like Python's doctest or Rust's doc tests, doctest-php automatically extracts and runs code examples from your documentation.

## Installation

```bash
composer require --dev doctest/doctest-php
```

## Quick Start

Add the following to your `phpunit.xml`:

```xml
<phpunit>
    <testsuites>
        <!-- Your existing test suites -->
        <testsuite name="Unit">
            <directory>tests</directory>
        </testsuite>

        <!-- Add doctest suite -->
        <testsuite name="Doctest">
            <file>vendor/doctest/doctest-php/DoctestSuite.php</file>
        </testsuite>
    </testsuites>

    <!-- Configure the extension -->
    <extensions>
        <bootstrap class="Doctest\DoctestExtension">
            <parameter name="directories" value="src"/>
        </bootstrap>
    </extensions>
</phpunit>
```

Write examples in your docblocks:

```php
<?php

/**
 * Add two numbers.
 *
 * ```php
 * add(1, 2) // => 3
 * add(-1, 1) // => 0
 * ```
 */
function add(int $a, int $b): int
{
    return $a + $b;
}
```

Run PHPUnit:

```bash
./vendor/bin/phpunit
```

That's it! No test files to create - doctests are automatically discovered and executed.

## Supported Assertion Formats

### Return Value Assertion (`// => expected`)

```php
/**
 * @example
 * add(1, 2) // => 3
 * subtract(5, 3) // => 2
 */
```

### Output Assertion (`// Output: expected`)

```php
/**
 * @example
 * echo greet("World"); // Output: Hello, World!
 */
```

### Exception Assertion (`// throws ExceptionClass`)

```php
/**
 * @example
 * divide(10, 0) // throws DivisionByZeroError
 *
 * @example With message check
 * divide(10, 0) // throws DivisionByZeroError: Cannot divide by zero
 */
```

### Smoke Test (No Assertion)

Code without assertions is treated as a smoke test - it passes if no exception is thrown:

```php
/**
 * @example
 * $repo = new UserRepository();
 * $name = $repo->findNameById(10);
 * echo $name;
 */
```

## Example Formats

### @example Tag

```php
/**
 * @example Basic usage
 * $calc = new Calculator();
 * $calc->add(1, 2) // => 3
 *
 * @example With negative numbers
 * $calc = new Calculator();
 * $calc->add(-5, 3) // => -2
 */
```

### Code Fence (```php)

````php
/**
 * Calculate the sum.
 *
 * ```php
 * $calc = new Calculator();
 * $calc->add(5, 3) // => 8
 * ```
 */
````

## Multi-line Examples with State

Variables persist within a single example block:

```php
/**
 * @example
 * $stack = new Stack();
 * $stack->push(1);
 * $stack->push(2);
 * $stack->pop() // => 2
 * $stack->pop() // => 1
 */
```

## Configuration Options

Configure via extension parameters in `phpunit.xml`:

```xml
<extensions>
    <bootstrap class="Doctest\DoctestExtension">
        <parameter name="directories" value="src,lib"/>
        <parameter name="files" value="functions.php,helpers.php"/>
        <parameter name="exclude" value="**/vendor/**,**/tests/**"/>
        <parameter name="bootstrap" value="tests/bootstrap.php"/>
    </bootstrap>
</extensions>
```

| Parameter | Description |
|-----------|-------------|
| `directories` | Comma-separated list of directories to scan |
| `files` | Comma-separated list of specific files to scan |
| `exclude` | Comma-separated glob patterns to exclude |
| `bootstrap` | Bootstrap file to load before running doctests |

## Requirements

- PHP 8.1+
- PHPUnit 10.0+ or 11.0+

## License

MIT
