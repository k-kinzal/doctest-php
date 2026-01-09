<?php

declare(strict_types=1);

namespace Doctest\Scanner;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;

/**
 * Scans PHP source files to extract targets (classes, methods, functions) with docblocks.
 *
 * Uses PHP-Parser to analyze the AST (Abstract Syntax Tree) of source files
 * and extract all elements that have documentation blocks.
 *
 * @example Creating a scanner
 *     $scanner = new \Doctest\Scanner\SourceScanner();
 *     $scanner instanceof \Doctest\Scanner\SourceScanner // => true
 */
final class SourceScanner
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory())->createForNewestSupportedVersion();
    }

    /**
     * Scans a PHP file and yields all targets with docblocks.
     *
     * Analyzes the file's AST to find file-level docblocks, classes, methods,
     * and functions that have documentation.
     *
     * @param string $filePath Absolute path to the PHP file
     * @return iterable<Target> Targets found in the file
     */
    public function scanFile(string $filePath): iterable
    {
        $code = file_get_contents($filePath);
        if ($code === false) {
            return;
        }

        try {
            $ast = $this->parser->parse($code);
        } catch (\Throwable) {
            return;
        }

        if ($ast === null) {
            return;
        }

        // Check for file-level docblock
        $fileDocblock = $this->extractFileDocblock($code);
        if ($fileDocblock !== null) {
            yield new Target(
                type: TargetType::File,
                filePath: $filePath,
                docblock: $fileDocblock,
                name: basename($filePath),
                line: 1,
            );
        }

        // Traverse AST for functions, classes, methods
        yield from $this->traverseAst($ast, $filePath);
    }

    private function extractFileDocblock(string $code): ?string
    {
        // Match the first docblock that appears before any code
        // It should appear after <?php and before any other code
        if (preg_match('/^<\?php\s*(\/\*\*.*?\*\/)/s', $code, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @param array<Node> $ast
     * @return iterable<Target>
     */
    private function traverseAst(array $ast, string $filePath): iterable
    {
        $visitor = new class ($filePath) extends NodeVisitorAbstract {
            /** @var list<Target> */
            public array $targets = [];
            private ?string $currentNamespace = null;
            private ?string $currentClass = null;

            public function __construct(
                private readonly string $filePath,
            ) {}

            public function enterNode(Node $node): ?int
            {
                if ($node instanceof Namespace_) {
                    $this->currentNamespace = $node->name?->toString();
                }

                if ($node instanceof Class_) {
                    $this->currentClass = $node->name?->toString();
                    $docComment = $node->getDocComment();

                    if ($docComment !== null && $this->currentClass !== null) {
                        $this->targets[] = new Target(
                            type: TargetType::Class_,
                            filePath: $this->filePath,
                            docblock: $docComment->getText(),
                            name: $this->currentClass,
                            line: $docComment->getStartLine(),
                            namespace: $this->currentNamespace,
                        );
                    }
                }

                if ($node instanceof ClassMethod) {
                    $docComment = $node->getDocComment();

                    if ($docComment !== null && $this->currentClass !== null) {
                        $this->targets[] = new Target(
                            type: TargetType::Method,
                            filePath: $this->filePath,
                            docblock: $docComment->getText(),
                            name: $node->name->toString(),
                            line: $docComment->getStartLine(),
                            namespace: $this->currentNamespace,
                            className: $this->currentClass,
                            isStatic: $node->isStatic(),
                        );
                    }
                }

                if ($node instanceof Function_) {
                    $docComment = $node->getDocComment();

                    if ($docComment !== null) {
                        $this->targets[] = new Target(
                            type: TargetType::Function_,
                            filePath: $this->filePath,
                            docblock: $docComment->getText(),
                            name: $node->name->toString(),
                            line: $docComment->getStartLine(),
                            namespace: $this->currentNamespace,
                        );
                    }
                }

                return null;
            }

            public function leaveNode(Node $node): ?int
            {
                if ($node instanceof Namespace_) {
                    $this->currentNamespace = null;
                }

                if ($node instanceof Class_) {
                    $this->currentClass = null;
                }

                return null;
            }
        };

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        yield from $visitor->targets;
    }
}
