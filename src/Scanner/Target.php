<?php

/**
 * Scanner target representation for doctest-php.
 *
 * @example File-level example: creating a class target
 *     $target = new \Doctest\Scanner\Target(
 *         type: \Doctest\Scanner\TargetType::Class_,
 *         filePath: '/app/src/MyClass.php',
 *         docblock: 'class docs',
 *         name: 'MyClass',
 *         line: 10,
 *     );
 *     $target->type // => \Doctest\Scanner\TargetType::Class_
 */

declare(strict_types=1);

namespace Doctest\Scanner;

/**
 * Represents a target (file, class, method, or function) that may contain docblock examples.
 *
 * A Target encapsulates all information needed to identify and process a documented
 * element in the source code, including its type, location, and associated docblock.
 *
 * @example Creating a method target and getting short name
 *     $target = new \Doctest\Scanner\Target(
 *         type: \Doctest\Scanner\TargetType::Method,
 *         filePath: '/path/to/File.php',
 *         docblock: 'some docs',
 *         name: 'myMethod',
 *         line: 10,
 *         namespace: 'App\\Services',
 *         className: 'Calculator',
 *         isStatic: false,
 *     );
 *     $target->getShortName() // => 'Calculator::myMethod()'
 *
 * @example Getting fully qualified name for a class
 *     $target = new \Doctest\Scanner\Target(
 *         type: \Doctest\Scanner\TargetType::Class_,
 *         filePath: '/path/to/File.php',
 *         docblock: 'some docs',
 *         name: 'Calculator',
 *         line: 5,
 *         namespace: 'App\\Services',
 *     );
 *     $target->getFullyQualifiedName() // => 'App\\Services\\Calculator'
 */
final class Target
{
    /**
     * @param TargetType $type The type of this target (file, class, method, or function)
     * @param string $filePath Absolute path to the source file
     * @param string $docblock The raw docblock content including delimiters
     * @param string $name The name of the target (filename, class name, method name, etc.)
     * @param int $line Line number where the docblock starts
     * @param string|null $namespace The namespace of the target, if applicable
     * @param string|null $className The class name, if the target is a method
     * @param bool $isStatic Whether the target is a static method
     */
    public function __construct(
        public readonly TargetType $type,
        public readonly string $filePath,
        public readonly string $docblock,
        public readonly string $name,
        public readonly int $line,
        public readonly ?string $namespace = null,
        public readonly ?string $className = null,
        public readonly bool $isStatic = false,
    ) {}

    /**
     * Returns the fully qualified name of the target.
     *
     * @example Getting FQN for a function
     *     $target = new \Doctest\Scanner\Target(
     *         type: \Doctest\Scanner\TargetType::Function_,
     *         filePath: '/path/to/helpers.php',
     *         docblock: 'some docs',
     *         name: 'calculate',
     *         line: 1,
     *         namespace: 'App\\Helpers',
     *     );
     *     $target->getFullyQualifiedName() // => 'App\\Helpers\\calculate()'
     */
    public function getFullyQualifiedName(): string
    {
        $fqn = $this->namespace !== null ? $this->namespace . '\\' : '';

        return match ($this->type) {
            TargetType::File => $this->filePath,
            TargetType::Class_ => $fqn . $this->name,
            TargetType::Function_ => $fqn . $this->name . '()',
            TargetType::Method => $fqn . $this->className . '::' . $this->name . '()',
        };
    }

    /**
     * Returns the short (unqualified) name of the target.
     *
     * @example Getting short name for a method
     *     $target = new \Doctest\Scanner\Target(
     *         type: \Doctest\Scanner\TargetType::Method,
     *         filePath: '/path/to/File.php',
     *         docblock: 'some docs',
     *         name: 'add',
     *         line: 10,
     *         namespace: 'App\\Services',
     *         className: 'Calculator',
     *     );
     *     $target->getShortName() // => 'Calculator::add()'
     */
    public function getShortName(): string
    {
        return match ($this->type) {
            TargetType::File => basename($this->filePath),
            TargetType::Class_ => $this->name,
            TargetType::Function_ => $this->name . '()',
            TargetType::Method => $this->className . '::' . $this->name . '()',
        };
    }
}
