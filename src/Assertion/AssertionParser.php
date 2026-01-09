<?php

/**
 * Assertion parsing for doctest-php.
 *
 * @example File-level example: creating parser instance
 *     $parser = new \Doctest\Assertion\AssertionParser();
 *     $parser instanceof \Doctest\Assertion\AssertionParser // => true
 */

declare(strict_types=1);

namespace Doctest\Assertion;

use Doctest\Parser\Example;

/**
 * Parses example code to extract statements and assertions.
 *
 * The parser recognizes several assertion formats:
 * - Return value: `expression // => expected_value`
 * - Output: `echo $var; // Output: expected_output`
 * - Exception: `throw new Exception(); // throws Exception`
 * - Exception with message: `throw new Exception("msg"); // throws Exception: msg`
 *
 * Lines without assertions become "smoke test" statements that just verify
 * the code runs without errors.
 */
final class AssertionParser
{
    /**
     * Parse example code and extract assertions.
     *
     * Supported formats:
     * - `expression // => expected_value`
     * - `echo $var; // Output: expected_output`
     * - `throw new Exception(); // throws Exception`
     * - `throw new Exception("msg"); // throws Exception: msg`
     *
     * @param Example $example The example to parse
     * @return ParsedExample The parsed example with statements
     */
    public function parse(Example $example): ParsedExample
    {
        $lines = explode("\n", $example->code);
        $statements = [];
        $currentCode = '';
        $lineNum = 0;

        foreach ($lines as $index => $line) {
            $trimmed = trim($line);

            // Skip empty lines
            if ($trimmed === '') {
                continue;
            }

            $lineNum = $index + 1;

            // Check for return value assertion: code // => expected
            if (preg_match('/^(.+?)\s*\/\/\s*=>\s*(.+)$/', $trimmed, $matches)) {
                $code = $currentCode . trim($matches[1]);
                $currentCode = '';

                $statements[] = new Statement(
                    code: $code,
                    assertion: new Assertion(
                        type: AssertionType::ReturnValue,
                        expectedRaw: trim($matches[2]),
                    ),
                    line: $lineNum,
                );
                continue;
            }

            // Check for output assertion: code // Output: expected
            if (preg_match('/^(.+?)\s*\/\/\s*Output:\s*(.*)$/', $trimmed, $matches)) {
                $code = $currentCode . trim($matches[1]);
                $currentCode = '';

                $statements[] = new Statement(
                    code: $code,
                    assertion: new Assertion(
                        type: AssertionType::Output,
                        expectedRaw: $matches[2],
                    ),
                    line: $lineNum,
                );
                continue;
            }

            // Check for exception assertion: code // throws ExceptionClass: message
            if (preg_match('/^(.+?)\s*\/\/\s*throws\s+(\S+)(?::\s*(.*))?$/', $trimmed, $matches)) {
                $code = $currentCode . trim($matches[1]);
                $currentCode = '';

                $statements[] = new Statement(
                    code: $code,
                    assertion: new Assertion(
                        type: AssertionType::Exception,
                        expectedRaw: $matches[2],
                        exceptionMessage: isset($matches[3]) ? trim($matches[3]) : null,
                    ),
                    line: $lineNum,
                );
                continue;
            }

            // Check if line continues (ends with operators or incomplete syntax)
            if ($this->isIncompleteLine($trimmed)) {
                $currentCode .= $trimmed . ' ';
                continue;
            }

            // Regular statement without assertion (smoke test)
            $code = $currentCode . $trimmed;
            $currentCode = '';

            $statements[] = new Statement(
                code: $code,
                assertion: null,
                line: $lineNum,
            );
        }

        // Handle any remaining code
        if ($currentCode !== '') {
            $statements[] = new Statement(
                code: trim($currentCode),
                assertion: null,
                line: $lineNum,
            );
        }

        return new ParsedExample($example, $statements);
    }

    private function isIncompleteLine(string $line): bool
    {
        // Check for lines that are likely incomplete
        $incompletePatterns = [
            '/\.\s*$/',      // Ends with .
            '/,\s*$/',       // Ends with ,
            '/\(\s*$/',      // Ends with (
            '/\[\s*$/',      // Ends with [
            '/\{\s*$/',      // Ends with {
            '/=>\s*$/',      // Ends with =>
            '/\->\s*$/',     // Ends with ->
            '/::\s*$/',      // Ends with ::
            '/&&\s*$/',      // Ends with &&
            '/\|\|\s*$/',    // Ends with ||
            '/\?\s*$/',      // Ends with ?
        ];

        foreach ($incompletePatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }
}
