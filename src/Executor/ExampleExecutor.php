<?php

/**
 * Example execution for doctest-php.
 *
 * @example File-level example: creating executor
 *     $executor = new \Doctest\Executor\ExampleExecutor();
 *     $executor instanceof \Doctest\Executor\ExampleExecutor // => true
 */

declare(strict_types=1);

namespace Doctest\Executor;

use Doctest\Assertion\AssertionParser;
use Doctest\Assertion\AssertionResult;
use Doctest\Assertion\AssertionType;
use Doctest\Assertion\ParsedExample;
use Doctest\Assertion\Statement;
use Doctest\Parser\Example;
use LogicException;
use ReflectionClass;
use Throwable;

/**
 * Executes doctest examples and verifies assertions.
 *
 * The executor handles parsing example code, executing statements,
 * and verifying assertions. It supports:
 * - Return value assertions (// =>)
 * - Output assertions (// Output:)
 * - Exception assertions (// throws)
 *
 * @example Creating and checking executor
 *     $executor = new \Doctest\Executor\ExampleExecutor();
 *     $executor instanceof \Doctest\Executor\ExampleExecutor // => true
 */
final class ExampleExecutor
{
    private AssertionParser $parser;

    /** @var list<string> */
    private array $loadedFiles = [];

    /**
     * @param string|null $bootstrap Path to bootstrap file to require before execution
     */
    public function __construct(
        private readonly ?string $bootstrap = null,
    ) {
        $this->parser = new AssertionParser();
    }

    /**
     * Executes an example and returns the result.
     *
     * Parses the example code, executes each statement, and verifies
     * any assertions.
     *
     * @param Example $example The example to execute
     * @return ExecutionResult The execution result
     */
    public function execute(Example $example): ExecutionResult
    {
        $parsed = $this->parser->parse($example);

        return $this->executeParsed($parsed);
    }

    /**
     * Executes a parsed example and returns the result.
     *
     * @param ParsedExample $parsed The parsed example to execute
     * @return ExecutionResult The execution result
     */
    public function executeParsed(ParsedExample $parsed): ExecutionResult
    {
        $context = new ExecutionContext();
        $failures = [];

        // Load bootstrap if configured and not already loaded
        if ($this->bootstrap !== null && !in_array($this->bootstrap, $this->loadedFiles, true)) {
            require_once $this->bootstrap;
            $this->loadedFiles[] = $this->bootstrap;
        }

        // Load the target file to make functions/classes available
        $targetFile = $parsed->example->target->filePath;
        if (!in_array($targetFile, $this->loadedFiles, true)) {
            require_once $targetFile;
            $this->loadedFiles[] = $targetFile;
        }

        foreach ($parsed->statements as $statement) {
            $result = $this->executeStatement($statement, $context);

            if (!$result->passed) {
                $failures[] = $result;
            }
        }

        return new ExecutionResult(
            example: $parsed->example,
            passed: $failures === [],
            failures: $failures,
        );
    }

    private function executeStatement(Statement $statement, ExecutionContext $context): AssertionResult
    {
        // Handle exception assertions specially
        if ($statement->assertion?->type === AssertionType::Exception) {
            return $this->executeWithExpectedException($statement, $context);
        }

        // Regular execution
        try {
            $result = $this->evaluateCode($statement->code, $context);

            if (!$statement->hasAssertion()) {
                // No assertion - smoke test, just ensure it runs without error
                return new AssertionResult(
                    passed: true,
                    message: '',
                    statement: $statement,
                );
            }

            return $this->checkAssertion($statement, $result, $context);
        } catch (Throwable $e) {
            return new AssertionResult(
                passed: false,
                message: sprintf(
                    'Unexpected exception %s: %s',
                    get_class($e),
                    $e->getMessage()
                ),
                statement: $statement,
            );
        }
    }

    private function executeWithExpectedException(Statement $statement, ExecutionContext $context): AssertionResult
    {
        $assertion = $statement->assertion;
        assert($assertion !== null);

        try {
            $this->evaluateCode($statement->code, $context);

            // No exception was thrown
            return new AssertionResult(
                passed: false,
                message: sprintf(
                    'Expected exception %s but none was thrown',
                    $assertion->expectedRaw
                ),
                statement: $statement,
            );
        } catch (Throwable $e) {
            $expectedClass = $assertion->expectedRaw;

            // Check if the exception is an instance of the expected class
            if (!($e instanceof $expectedClass) && !is_a($e, $expectedClass)) {
                // Try with full namespace resolution
                if (class_exists($expectedClass) || interface_exists($expectedClass)) {
                    if (!($e instanceof $expectedClass)) {
                        return new AssertionResult(
                            passed: false,
                            message: sprintf(
                                'Expected exception %s but got %s: %s',
                                $expectedClass,
                                get_class($e),
                                $e->getMessage()
                            ),
                            statement: $statement,
                            expected: $expectedClass,
                            actual: get_class($e),
                        );
                    }
                } else {
                    // Check by class name only (without namespace)
                    $actualClassName = (new ReflectionClass($e))->getShortName();
                    if ($actualClassName !== $expectedClass && get_class($e) !== $expectedClass) {
                        return new AssertionResult(
                            passed: false,
                            message: sprintf(
                                'Expected exception %s but got %s: %s',
                                $expectedClass,
                                get_class($e),
                                $e->getMessage()
                            ),
                            statement: $statement,
                            expected: $expectedClass,
                            actual: get_class($e),
                        );
                    }
                }
            }

            // Check exception message if specified
            if ($assertion->exceptionMessage !== null) {
                if (!str_contains($e->getMessage(), $assertion->exceptionMessage)) {
                    return new AssertionResult(
                        passed: false,
                        message: sprintf(
                            'Exception message "%s" does not contain "%s"',
                            $e->getMessage(),
                            $assertion->exceptionMessage
                        ),
                        statement: $statement,
                        expected: $assertion->exceptionMessage,
                        actual: $e->getMessage(),
                    );
                }
            }

            return new AssertionResult(
                passed: true,
                message: '',
                statement: $statement,
            );
        }
    }

    private function evaluateCode(string $code, ExecutionContext $context): mixed
    {
        // Get existing variables from context
        $__doctest_vars__ = $context->getVariables();

        // Determine if we should return the expression result
        $needsReturn = $this->codeNeedsReturn($code);

        // Build the code to evaluate
        // We use a closure-like approach to manage variable scope
        $__doctest_code__ = $needsReturn ? 'return ' . $code . ';' : $code;

        // Start output buffering
        ob_start();

        try {
            // Extract variables into local scope
            extract($__doctest_vars__, EXTR_SKIP);

            // Execute the code
            $__doctest_result__ = eval($__doctest_code__);

            // Capture any new/modified variables
            // We need to get all defined vars and filter out our internal ones
            $__doctest_all_vars__ = get_defined_vars();

            // Filter out internal variables
            unset(
                $__doctest_all_vars__['__doctest_vars__'],
                $__doctest_all_vars__['__doctest_code__'],
                $__doctest_all_vars__['__doctest_result__'],
                $__doctest_all_vars__['__doctest_all_vars__'],
                $__doctest_all_vars__['needsReturn'],
                $__doctest_all_vars__['code'],
                $__doctest_all_vars__['context'],
            );

            // Update context with the captured variables
            $context->setVariables($__doctest_all_vars__);
            $context->lastOutput = ob_get_contents() ?: '';

            return $__doctest_result__;
        } finally {
            ob_end_clean();
        }
    }

    private function codeNeedsReturn(string $code): bool
    {
        $trimmed = trim($code);

        // Don't add return for statements that already have side effects
        $noReturnPatterns = [
            '/^\$\w+\s*=/',           // Variable assignment
            '/^echo\s/',              // echo statements
            '/^print\s/',             // print statements
            '/^return\s/',            // Already has return
            '/^if\s*\(/',             // if statements
            '/^for\s*\(/',            // for loops
            '/^foreach\s*\(/',        // foreach loops
            '/^while\s*\(/',          // while loops
            '/^do\s*\{/',             // do-while loops
            '/^switch\s*\(/',         // switch statements
            '/^try\s*\{/',            // try blocks
            '/^throw\s/',             // throw statements
            '/^class\s/',             // class declarations
            '/^function\s/',          // function declarations
            '/^new\s+\w+\s*;$/',      // New object without assignment
        ];

        foreach ($noReturnPatterns as $pattern) {
            if (preg_match($pattern, $trimmed)) {
                return false;
            }
        }

        // Check if it ends with a semicolon (statement) vs expression
        if (str_ends_with($trimmed, ';')) {
            // Could be either, but let's check for method calls that return void
            return false;
        }

        return true;
    }

    private function checkAssertion(Statement $statement, mixed $actual, ExecutionContext $context): AssertionResult
    {
        $assertion = $statement->assertion;
        assert($assertion !== null);

        return match ($assertion->type) {
            AssertionType::ReturnValue => $this->checkReturnValue($statement, $actual),
            AssertionType::Output => $this->checkOutput($statement, $context->lastOutput),
            AssertionType::Exception => throw new LogicException('Exception assertions should be handled separately'),
        };
    }

    private function checkReturnValue(Statement $statement, mixed $actual): AssertionResult
    {
        $assertion = $statement->assertion;
        assert($assertion !== null);

        // Parse the expected value
        $expectedRaw = $assertion->expectedRaw;

        try {
            $expected = eval('return ' . $expectedRaw . ';');
        } catch (Throwable $e) {
            return new AssertionResult(
                passed: false,
                message: sprintf(
                    'Failed to parse expected value: %s',
                    $e->getMessage()
                ),
                statement: $statement,
            );
        }

        // Strict comparison
        if ($expected === $actual) {
            return new AssertionResult(
                passed: true,
                message: '',
                statement: $statement,
            );
        }

        return new AssertionResult(
            passed: false,
            message: 'Values do not match',
            statement: $statement,
            expected: $expected,
            actual: $actual,
        );
    }

    private function checkOutput(Statement $statement, string $actualOutput): AssertionResult
    {
        $assertion = $statement->assertion;
        assert($assertion !== null);

        $expected = $assertion->expectedRaw;

        if ($expected === $actualOutput || $expected === rtrim($actualOutput)) {
            return new AssertionResult(
                passed: true,
                message: '',
                statement: $statement,
            );
        }

        return new AssertionResult(
            passed: false,
            message: 'Output does not match',
            statement: $statement,
            expected: $expected,
            actual: $actualOutput,
        );
    }
}
