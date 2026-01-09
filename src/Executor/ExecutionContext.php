<?php

declare(strict_types=1);

namespace Doctest\Executor;

/**
 * Maintains state across statement executions within an example.
 *
 * ExecutionContext tracks variables defined during example execution,
 * allowing statements to share state. It also captures output from
 * echo/print statements for output assertions.
 *
 * @example Checking variable existence
 *     $ctx = new \Doctest\Executor\ExecutionContext();
 *     $ctx->setVariable('x', 42);
 *     $ctx->hasVariable('x') // => true
 *
 * @example Getting variable value
 *     $ctx = new \Doctest\Executor\ExecutionContext();
 *     $ctx->setVariable('y', 99);
 *     $ctx->getVariable('y') // => 99
 */
final class ExecutionContext
{
    /** @var array<string, mixed> */
    private array $variables = [];

    /** Output captured from the last statement execution */
    public string $lastOutput = '';

    /**
     * Returns all variables defined in this context.
     *
     * @return array<string, mixed>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * Sets multiple variables at once, filtering internal variables.
     *
     * @param array<string, mixed> $vars Variables to set
     */
    public function setVariables(array $vars): void
    {
        // Filter out internal variables used by ExampleExecutor
        $internalVars = [
            '__doctest_result__',
            '__doctest_code__',
            '__doctest_context__',
            '__doctest_vars__',
            '__doctest_output__',
            'variables',
            'needsReturn',
            'evalCode',
        ];

        foreach ($internalVars as $name) {
            unset($vars[$name]);
        }

        $this->variables = $vars;
    }

    /**
     * Sets a single variable in the context.
     */
    public function setVariable(string $name, mixed $value): void
    {
        $this->variables[$name] = $value;
    }

    /**
     * Gets a variable from the context.
     *
     * Returns null if the variable is not set.
     */
    public function getVariable(string $name): mixed
    {
        return $this->variables[$name] ?? null;
    }

    /**
     * Checks if a variable exists in the context.
     */
    public function hasVariable(string $name): bool
    {
        return array_key_exists($name, $this->variables);
    }
}
