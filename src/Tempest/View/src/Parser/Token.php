<?php

namespace Tempest\View\Parser;

class Token
{
    private(set) array $children = [];
    private(set) ?Token $parent = null;
    private(set) ?Token $closingToken = null;
    private(set) array $attributes = [];

    public function __construct(
        public readonly string $content,
        public readonly TokenType $type,
    ) {}

    public function addChild(Token $other): void
    {
        $this->children[] = $other;
        $other->parent = $this;
    }

    public function addAttribute(string $name): void
    {
        $this->attributes[$name] = true;
    }

    public function setAttributeValue(string $name, string $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function setCLosingToken(Token $closingToken): void
    {
        $this->closingToken = $closingToken;
    }

    public function compile(): string
    {
        $buffer = $this->content;

        foreach ($this->attributes as $name => $value) {
            if ($value !== true) {
                $buffer .= $name . $value;
            } else {
                $buffer .= $name;
            }
        }

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
                str_replace('\'', "\\'", $this->content),
                $this->type->name,
            ),
        ];
    }
}