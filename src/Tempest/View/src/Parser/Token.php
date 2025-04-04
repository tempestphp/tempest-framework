<?php

namespace Tempest\View\Parser;

use function Tempest\Support\str;

final class Token
{
    private(set) array $children = [];

    private(set) ?Token $parent = null;

    private(set) ?Token $endingToken = null;

    private(set) ?Token $closingToken = null;

    private(set) array $rawTagContent = [];

    private(set) array $attributes = [];

    private(set) ?string $tag = null;

    public function __construct(
        public readonly string $content,
        public readonly TokenType $type,
    ) {
        $this->tag = (match ($this->type) {
            TokenType::OPEN_TAG_START => str($this->content)
                ->afterFirst('<')
                ->before(['>', ' ', PHP_EOL]),
            TokenType::SELF_CLOSING_TAG => str($this->content)
                ->afterFirst('<')
                ->before(['/', ' ', PHP_EOL]),
            TokenType::CLOSING_TAG => str($this->content)
                ->afterFirst('/')
                ->before(['>', ' ', PHP_EOL]),
            default => null,
        })
            ?->trim()
            ->lower()
            ->toString();
    }

    public function addChild(Token $other): void
    {
        $this->children[] = $other;
        $other->parent = $this;
    }

    public function getAttribute(string $name): null|string|bool
    {
        return $this->attributes[$name] ?? null;
    }

    public function addTagContent(string $content): void
    {
        $this->rawTagContent[] = $content;
    }

    public function addAttribute(string $name): void
    {
        $this->addTagContent($name);
        $this->attributes[$this->attributeName($name)] = true;
    }

    public function setAttributeValue(string $name, string $value): void
    {
        $this->addTagContent($value);
        $this->attributes[$this->attributeName($name)] = $this->attributeValue($value);
    }

    public function setEndingToken(Token $endingToken): void
    {
        $this->endingToken = $endingToken;
    }

    public function setClosingToken(Token $closingToken): void
    {
        $this->closingToken = $closingToken;
    }

    public function compile(): string
    {
        $buffer = $this->content;

        $buffer .= $this->compileAttributes();

        $buffer .= $this->endingToken?->compile();

        $buffer .= $this->compileChildren();

        return $buffer . $this->closingToken?->compile();
    }

    public function compileAttributes(): string
    {
        return implode('', $this->rawTagContent);
    }

    public function compileChildren(): string
    {
        $buffer = '';

        foreach ($this->children as $child) {
            $buffer .= $child->compile();
        }

        return $buffer;
    }

    public function __debugInfo(): array
    {
        return [
            sprintf(
                "new Token('%s', TokenType::%s)",
                str_replace("'", "\\'", $this->content),
                $this->type->name,
            ),
        ];
    }

    private function attributeName(string $name): string
    {
        return str($name)->trim()->before('=')->toString();
    }

    private function attributeValue(string $value): string
    {
        return str($value)->afterFirst('"')->beforeLast('"')->toString();
    }
}
