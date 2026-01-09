<?php

declare(strict_types=1);

namespace Doctest\Assertion;

/**
 * Types of assertions that can be made in doctest examples.
 *
 * @example Return value assertion type
 *     \Doctest\Assertion\AssertionType::ReturnValue->value // => 'return'
 *
 * @example Output assertion type
 *     \Doctest\Assertion\AssertionType::Output->value // => 'output'
 *
 * @example Exception assertion type
 *     \Doctest\Assertion\AssertionType::Exception->value // => 'exception'
 */
enum AssertionType: string
{
    /** Asserts that an expression returns a specific value */
    case ReturnValue = 'return';

    /** Asserts that code produces specific output */
    case Output = 'output';

    /** Asserts that code throws a specific exception */
    case Exception = 'exception';
}
