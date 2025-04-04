<?php

namespace Tempest\View\Parser;

use Tempest\Highlight\Themes\TerminalStyle;

class Token
{
    private(set) array $children = [];
    private(set) ?Token $parent = null;
    private(set) ?Token $closingToken = null;

    public function __construct(
        public readonly string $content,
        public readonly TokenType $type,
    ) {}

    public function addChild(Token $other): void
    {
        $this->children[] = $other;
        $other->parent = $this;
    }

    public function setCLosingToken(Token $closingToken): void
    {
        $this->closingToken = $closingToken;
    }

    public function compile(): string
    {
        $buffer = $this->content;

        foreach ($this->children as $child) {
            $buffer .= $child->compile();
        }

        $buffer .= $this->closingToken?->compile();

        return $buffer;
    }

    public function __debugInfo(): ?array
    {
        return [
            sprintf(
                'new Token(\'%s\', TokenType::%s)',
                addslashes($this->content),
                $this->type->name,
            ),
        ];
    }
}