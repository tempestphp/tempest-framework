<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;

/**
 * A method body that was given as source code. It keeps its original formatting when printed,
 * only the names that were simplified are replaced.
 *
 * @internal
 */
final class MethodBody
{
    public const string ATTRIBUTE = 'tempest.methodBody';

    private const string OPEN_TAG = "<?php\n";

    /** @var array<int, array{int, string}> replacements keyed by their offset, with the replaced length and code */
    private array $replacements = [];

    /**
     * @param array<int, true> $verbatimLines lines whose indentation is part of a string's value
     */
    private function __construct(
        private readonly string $code,
        private readonly int $indentation,
        private readonly array $verbatimLines,
        private readonly string $comments,
    ) {}

    /**
     * @return array{self, Stmt[]}
     */
    public static function parse(string $code): array
    {
        $code = trim($code, "\n");

        [$stmts, $tokens] = SourceParser::parse(self::OPEN_TAG . $code);

        $lines = explode("\n", $code);
        $verbatimLines = self::verbatimLines($stmts, $lines);
        $comments = '';

        foreach ($tokens as $token) {
            if (! $token->is([T_COMMENT, T_DOC_COMMENT])) {
                continue;
            }

            $comments .= $token->text . "\n";
        }

        return [new self($code, self::indentationOf($lines, $verbatimLines), $verbatimLines, $comments), $stmts];
    }

    /**
     * Replaces a name that was parsed from this body.
     */
    public function replaceName(Name $name, Name $replacement): void
    {
        $start = $name->getStartFilePos();

        if ($start < 0) {
            return;
        }

        $this->replacements[$start - strlen(self::OPEN_TAG)] = [$name->getEndFilePos() - $start + 1, $replacement->toCodeString()];
    }

    public function comments(): string
    {
        return $this->comments;
    }

    /**
     * Prints the body with its own indentation replaced by the given one.
     */
    public function print(string $indentation): string
    {
        $code = $this->code;
        $replacements = $this->replacements;

        krsort($replacements);

        foreach ($replacements as $offset => [$length, $replacement]) {
            $code = substr_replace($code, $replacement, $offset, $length);
        }

        $lines = explode("\n", $code);

        foreach ($lines as $index => $line) {
            if ($line === '' || isset($this->verbatimLines[$index])) {
                continue;
            }

            $lines[$index] = $indentation . substr($line, min($this->indentation, strspn($line, " \t")));
        }

        return implode("\n", $lines);
    }

    /**
     * Finds the lines whose indentation is part of a string's value. Heredocs may be reindented as a whole,
     * as long as they are indented with spaces only, since PHP strips the indentation of their closing marker.
     *
     * @param Stmt[] $stmts
     * @param string[] $lines
     * @return array<int, true>
     */
    private static function verbatimLines(array $stmts, array $lines): array
    {
        $strings = new NodeFinder()->find(
            $stmts,
            static fn (Node $node) => (
                ($node instanceof Scalar\String_ || $node instanceof Scalar\InterpolatedString || $node instanceof Expr\ShellExec)
                && $node->getStartLine() !== $node->getEndLine()
            ),
        );

        $verbatimLines = [];

        foreach ($strings as $string) {
            // The parser counts the opening tag as the first line, and a string's first line keeps its indentation.
            $stringLines = range($string->getStartLine() - 1, $string->getEndLine() - 2);
            $isHeredoc = in_array($string->getAttribute('kind'), [Scalar\String_::KIND_HEREDOC, Scalar\String_::KIND_NOWDOC], strict: true);

            if ($isHeredoc && ! self::hasTabIndentation(array_intersect_key($lines, array_flip($stringLines)))) {
                continue;
            }

            $verbatimLines += array_fill_keys($stringLines, true);
        }

        return $verbatimLines;
    }

    /**
     * @param string[] $lines
     * @param array<int, true> $verbatimLines
     */
    private static function indentationOf(array $lines, array $verbatimLines): int
    {
        $indentation = PHP_INT_MAX;

        foreach ($lines as $index => $line) {
            if (isset($verbatimLines[$index]) || trim($line) === '') {
                continue;
            }

            $indentation = min($indentation, strspn($line, " \t"));
        }

        return $indentation === PHP_INT_MAX ? 0 : $indentation;
    }

    /**
     * @param string[] $lines
     */
    private static function hasTabIndentation(array $lines): bool
    {
        return array_any($lines, static fn (string $line) => str_contains(substr($line, 0, strspn($line, " \t")), "\t"));
    }
}
