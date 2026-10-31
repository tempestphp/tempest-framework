<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\PhpVersion;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\Token;

/**
 * Prints PHP code following Tempest's code style. Nodes that were not changed keep their original formatting.
 *
 * @internal
 */
final class Printer extends Standard
{
    private const string BLANK_LINE_ATTRIBUTE = 'tempest.blankLine';

    private const string BLANK_LINE_MARKER = "\0tempest-blank-line\0";

    private bool $multilineParameters = false;

    public function __construct()
    {
        parent::__construct([
            'phpVersion' => PhpVersion::fromComponents(8, 5),
            'shortArraySyntax' => true,
        ]);
    }

    /**
     * A statement that separates the surrounding statements by a blank line when they are inserted into existing code.
     */
    public static function blankLine(): Stmt\Nop
    {
        return new Stmt\Nop([self::BLANK_LINE_ATTRIBUTE => true]);
    }

    public static function isBlankLine(Node $node): bool
    {
        return $node instanceof Stmt\Nop && $node->getAttribute(self::BLANK_LINE_ATTRIBUTE) === true;
    }

    /**
     * @param Stmt[] $stmts
     */
    public function printNewFile(array $stmts): string
    {
        $this->resetState();
        $this->preprocessNodes($stmts);

        return '<?php' . $this->newline . $this->newline . $this->pBlocks($stmts) . $this->newline;
    }

    /**
     * @param Stmt[] $stmts
     * @param Stmt[] $originalStmts
     * @param Token[] $tokens
     */
    public function printChangedFile(array $stmts, array $originalStmts, array $tokens): string
    {
        $code = $this->printFormatPreserving($stmts, $originalStmts, $tokens);
        $code = preg_replace('/[ \t]*' . self::BLANK_LINE_MARKER . '/', '', $code);

        return str_ends_with($code, $this->newline) ? $code : $code . $this->newline;
    }

    #[Override]
    protected function p(Node $node, int $precedence = self::MAX_PRECEDENCE, int $lhsPrecedence = self::MAX_PRECEDENCE, bool $parentFormatPreserved = false): string
    {
        if ($this->origTokens === null) {
            return $this->{'p' . $node->getType()}($node, $precedence, $lhsPrecedence);
        }

        $original = $node->getAttribute('origNode');
        $canBeCopied = $parentFormatPreserved || ! $node instanceof Stmt\InlineHTML;

        if ($original instanceof Node && $canBeCopied && Changes::isUnchanged($node)) {
            $start = $original->getStartTokenPos();
            $indentAdjustment = $this->indentLevel - $this->origTokens->getIndentationBefore($start);

            return $this->origTokens->getTokenCode($start, $original->getEndTokenPos() + 1, $indentAdjustment);
        }

        return parent::p($node, $precedence, $lhsPrecedence, $parentFormatPreserved);
    }

    #[Override]
    protected function pArray(array $nodes, array $origNodes, int &$pos, int $indentAdjustment, string $parentNodeClass, string $subNodeName, ?int $fixup): ?string
    {
        if ($subNodeName !== 'attrGroups' || $nodes !== [] || $origNodes === []) {
            return parent::pArray($nodes, $origNodes, $pos, $indentAdjustment, $parentNodeClass, $subNodeName, $fixup);
        }

        $code = $this->origTokens->getTokenCode($pos, $origNodes[0]->getStartTokenPos(), $indentAdjustment);
        $pos = $this->origTokens->skipRightWhitespace($origNodes[array_key_last($origNodes)]->getEndTokenPos() + 1);

        return $code;
    }

    #[Override]
    protected function pStmt_Nop(Stmt\Nop $node): string
    {
        return self::isBlankLine($node) ? self::BLANK_LINE_MARKER : '';
    }

    #[Override]
    protected function pStmt_Declare(Stmt\Declare_ $node): string
    {
        $stmts = $node->stmts;

        return 'declare(' . $this->pCommaSeparated($node->declares) . ')' . ($stmts !== null ? ' {' . $this->pStmts($stmts) . $this->nl . '}' : ';');
    }

    #[Override]
    protected function pStmt_Namespace(Stmt\Namespace_ $node): string
    {
        $name = $node->name;

        if (! $this->canUseSemicolonNamespaces || $name === null) {
            return parent::pStmt_Namespace($node);
        }

        $stmts = $this->pBlocks($node->stmts);

        return 'namespace ' . $this->p($name) . ';' . ($stmts !== '' ? $this->newline . $this->nl . $stmts : '');
    }

    #[Override]
    protected function pClassCommon(Stmt\Class_ $node, string $afterClassToken): string
    {
        $extends = $node->extends;
        $declaration =
            $this->pAttrGroups($node->attrGroups, $node->name === null)
            . $this->pModifiers($node->flags)
            . 'class'
            . $afterClassToken
            . ($extends !== null ? ' extends ' . $this->p($extends) : '')
            . ($node->implements !== [] ? ' implements ' . $this->pCommaSeparated($node->implements) : '');

        $this->indent();
        $members = $this->pBlocks($node->stmts);
        $this->outdent();

        if ($members === '') {
            return $declaration . ' {}';
        }

        return $declaration . $this->nl . '{' . $this->newline . $this->indentation(1) . $members . $this->nl . '}';
    }

    #[Override]
    protected function pStmt_ClassMethod(Stmt\ClassMethod $node): string
    {
        $multiline = $this->shouldBreakParameters($node->params);
        $returnType = $node->returnType;

        $signature =
            $this->pAttrGroups($node->attrGroups)
            . $this->pModifiers($node->flags)
            . 'function '
            . ($node->byRef ? '&' : '')
            . $node->name
            . '('
            . $this->pParams($node->params)
            . ')'
            . ($returnType !== null ? ': ' . $this->p($returnType) : '');

        if ($node->stmts === null) {
            return $signature . ';';
        }

        $body = $this->pMethodBody($node);

        if ($body === '') {
            return $signature . ' {}';
        }

        return $signature . ($multiline ? ' {' : $this->nl . '{') . $body . $this->nl . '}';
    }

    #[Override]
    protected function pParams(array $params): string
    {
        if (! $this->shouldBreakParameters($params)) {
            return $this->pCommaSeparated($params);
        }

        $wasMultiline = $this->multilineParameters;
        $this->multilineParameters = true;
        $code = $this->pCommaSeparatedMultiline($params, trailingComma: true) . $this->nl;
        $this->multilineParameters = $wasMultiline;

        return $code;
    }

    #[Override]
    protected function pParam(Node\Param $node): string
    {
        if (! $this->multilineParameters || $node->attrGroups === []) {
            return parent::pParam($node);
        }

        $attributes = '';

        foreach ($node->attrGroups as $group) {
            $attributes .= $this->p($group) . $this->nl;
        }

        return $attributes . parent::pParam(clone($node, ['attrGroups' => []]));
    }

    /**
     * Prints statements separated by blank lines, keeping related statements such as imports together.
     *
     * @param Node[] $nodes
     */
    private function pBlocks(array $nodes): string
    {
        $code = '';
        $previous = null;

        foreach ($nodes as $node) {
            if (self::isBlankLine($node)) {
                continue;
            }

            if ($previous !== null) {
                $code .= $this->belongTogether($previous, $node) ? $this->nl : $this->newline . $this->nl;
            }

            $comments = $node->getComments();

            if ($node instanceof Stmt\Nop) {
                $code .= $this->pComments($comments);
            } else {
                $code .= ($comments !== [] ? $this->pComments($comments) . $this->nl : '') . $this->p($node);
            }

            $previous = $node;
        }

        return $code;
    }

    private function belongTogether(Node $previous, Node $node): bool
    {
        if ($previous instanceof Stmt\Use_ && $node instanceof Stmt\Use_) {
            return $previous->type === $node->type;
        }

        return $previous::class === $node::class && ($node instanceof Stmt\TraitUse || $node instanceof Stmt\EnumCase);
    }

    private function pMethodBody(Stmt\ClassMethod $node): string
    {
        $methodBody = $node->getAttribute(MethodBody::ATTRIBUTE);

        if (! $methodBody instanceof MethodBody) {
            return $this->pStmts($node->stmts ?? []);
        }

        $code = $methodBody->print($this->indentation(1));

        return $code !== '' ? $this->newline . $code : '';
    }

    /**
     * @param Node\Param[] $params
     */
    private function shouldBreakParameters(array $params): bool
    {
        return array_any($params, static fn (Node\Param $param) => $param->flags !== 0 || $param->attrGroups !== [] || $param->getComments() !== []);
    }

    private function indentation(int $additionalLevels): string
    {
        return substr($this->nl, strlen($this->newline)) . str_repeat('    ', $additionalLevels);
    }
}
