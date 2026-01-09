<?php

declare(strict_types=1);

namespace Doctest\Scanner;

/**
 * Represents the type of target that contains docblock examples.
 *
 * Used to categorize where examples are found in the source code.
 *
 * @example Checking target type value
 *     TargetType::Method->value // => 'method'
 *
 * @example Comparing target types
 *     TargetType::Class_->value // => 'class'
 *     TargetType::Function_->value // => 'function'
 *     TargetType::File->value // => 'file'
 */
enum TargetType: string
{
    case File = 'file';
    case Class_ = 'class';
    case Method = 'method';
    case Function_ = 'function';
}
