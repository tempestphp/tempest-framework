<?php

namespace Tempest\View\Parser;

use Tempest\View\Exceptions\ClosingTagWasInvalid;

use function Tempest\Support\str;

final class Token
{
    /** @var \Tempest\View\Parser\Token[] */
    private(set) array $children = [];

    private(set) ?Token $parent = null;

    private(set) ?Token $endingToken = null;

    private(set) ?Token $closingToken = null;

    /** @var \Tempest\View\Parser\Token[] */
    private(set) array $rawAttributes = [];

    private(set) array $phpAttributes = [];

    private(set) array $htmlAttributes = [];

    private(set) ?string $tag = null;

    public function __construct(
        public readonly string $content,
        public readonly TokenType $type,
        public readonly int $line = 1,
        public readonly ?string $sourcePath = null,
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
        return $this->htmlAttributes[$name] ?? null;
    }

    public function addAttribute(Token $token): void
    {
        $this->rawAttributes[] = $token;

        if ($token->type === TokenType::ATTRIBUTE_NAME) {
            $this->htmlAttributes[$this->attributeName($token->content)] = '';
        } elseif ($token->type === TokenType::PHP) {
            $this->phpAttributes[] = $token->content;
        }
    }

    public function setAttributeValue(string $name, Token $token): void
    {
        $this->rawAttributes[] = $token;
        $this->htmlAttributes[$this->attributeName($name)] = $this->attributeValue($token->content);
    }

    public function setEndingToken(Token $endingToken): void
    {
        $this->endingToken = $endingToken;
    }

    public function setClosingToken(Token $closingToken): void
    {
        if ($closingToken->tag && $this->tag !== $closingToken->tag) {
            throw new ClosingTagWasInvalid($this->tag, $closingToken->tag);
        }

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
        $buffer = '';

        foreach ($this->rawAttributes as $rawAttribute) {
            $buffer .= $rawAttribute->content;
        }

        return $buffer;
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
        $value = str($value);

        $quote = $value->startsWith('"') ? '"' : "'";

        return str($value)->afterFirst($quote)->beforeLast($quote)->toString();
    }
}
