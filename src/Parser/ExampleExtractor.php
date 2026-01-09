<?php

declare(strict_types=1);

namespace Doctest\Parser;

use Doctest\Scanner\Target;

/**
 * Extracts Example objects from Target docblocks.
 *
 * Supports both at-example tags followed by indented code,
 * and triple-backtick php code fences in markdown style.
 *
 * @example Creating an extractor
 *     $extractor = new \Doctest\Parser\ExampleExtractor();
 *     $extractor instanceof \Doctest\Parser\ExampleExtractor // => true
 */
final class ExampleExtractor
{
    /**
     * Extracts all examples from a target's docblock.
     *
     * @param Target $target The target containing the docblock
     * @return iterable<Example> Examples found in the docblock
     */
    public function extract(Target $target): iterable
    {
        $docblock = $this->cleanDocblock($target->docblock);
        $index = 0;

        // Strategy 1: Extract @example blocks
        yield from $this->extractExampleTags($docblock, $target, $index);

        // Strategy 2: Extract ```php code fences
        yield from $this->extractCodeFences($docblock, $target, $index);
    }

    private function cleanDocblock(string $docblock): string
    {
        // Remove /** and */
        $docblock = preg_replace('/^\/\*\*\s*|\s*\*\/$/s', '', $docblock) ?? $docblock;

        // Remove leading * from each line
        $lines = explode("\n", $docblock);
        $cleaned = [];

        foreach ($lines as $line) {
            // Remove leading whitespace and asterisk
            $cleaned[] = preg_replace('/^\s*\*\s?/', '', $line) ?? $line;
        }

        return implode("\n", $cleaned);
    }

    /**
     * @return iterable<Example>
     */
    private function extractExampleTags(string $docblock, Target $target, int &$index): iterable
    {
        // Split docblock by @example tags
        $parts = preg_split('/(?=@example\b)/', $docblock, -1, PREG_SPLIT_NO_EMPTY);
        if ($parts === false) {
            return;
        }

        foreach ($parts as $part) {
            if (!str_starts_with(trim($part), '@example')) {
                continue;
            }

            $lines = explode("\n", $part);
            $firstLine = array_shift($lines);
            assert($firstLine !== null); // explode always returns at least one element

            // Extract optional description from @example line
            $description = null;
            if (preg_match('/@example\s+(.+)$/', $firstLine, $descMatch)) {
                $description = trim($descMatch[1]);
            }

            // Collect code lines until next @tag or end
            $codeLines = [];
            foreach ($lines as $line) {
                $trimmedLine = trim($line);
                // Stop at next @tag
                if (preg_match('/^@\w+/', $trimmedLine)) {
                    break;
                }
                // Skip empty lines at the beginning
                if ($codeLines === [] && $trimmedLine === '') {
                    continue;
                }
                $codeLines[] = $line;
            }

            // Trim trailing empty lines
            while ($codeLines !== [] && trim(end($codeLines)) === '') {
                array_pop($codeLines);
            }

            $code = implode("\n", $codeLines);
            $code = trim($code);

            if ($code === '') {
                continue;
            }

            // Calculate line number based on offset
            $offset = strpos($docblock, $part);
            $lineNumber = $this->calculateLineNumber($docblock, $offset !== false ? $offset : 0, $target->line);

            yield new Example(
                code: $code,
                target: $target,
                lineNumber: $lineNumber,
                index: $index++,
                description: $description !== '' ? $description : null,
            );
        }
    }

    /**
     * @return iterable<Example>
     */
    private function extractCodeFences(string $docblock, Target $target, int &$index): iterable
    {
        // Match ```php ... ``` blocks
        $pattern = '/```php\s*\n(.*?)```/s';

        if (preg_match_all($pattern, $docblock, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            foreach ($matches as $match) {
                /** @var array{0: array{0: string, 1: int}, 1: array{0: string, 1: int}} $match */
                $code = trim($match[1][0]);

                if ($code === '') {
                    continue;
                }

                $lineNumber = $this->calculateLineNumber($docblock, $match[0][1], $target->line);

                yield new Example(
                    code: $code,
                    target: $target,
                    lineNumber: $lineNumber,
                    index: $index++,
                );
            }
        }
    }

    private function calculateLineNumber(string $docblock, int $offset, int $baseLine): int
    {
        $beforeMatch = substr($docblock, 0, $offset);
        $linesBeforeMatch = substr_count($beforeMatch, "\n");

        return $baseLine + $linesBeforeMatch + 1;
    }
}
