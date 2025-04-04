<?php

namespace Tempest\View\Parser;

use function Tempest\Support\Html\is_void_tag;

final class TempestViewParser
{
    private array $scope = [];

    private ?Token $currentScope {
        get => $this->scope[array_key_last($this->scope)] ?? null;
    }

    public function __construct(
        private readonly TokenCollection $tokens,
    ) {}

    public static function ast(string $template): TempestViewAst
    {
        return new self(new TempestViewLexer($template)->lex())->parse();
    }

    public function parse(): TempestViewAst
    {
        $ast = new TempestViewAst();

        $currentAttribute = null;

        foreach ($this->tokens as $token) {
            if ($this->currentScope === null) {
                $ast->add($token);
            }

            if ($token->type === TokenType::OPEN_TAG_START) {
                $this->currentScope?->addChild($token);
                $this->openScope($token);
            } elseif ($token->type === TokenType::ATTRIBUTE_NAME) {
                $currentAttribute = $token->content;
                $this->currentScope?->addAttribute($currentAttribute);
            } elseif ($token->type === TokenType::ATTRIBUTE_VALUE) {
                $this->currentScope?->setAttributeValue($currentAttribute, $token->content);
                $currentAttribute = null;
            } elseif ($token->type === TokenType::OPEN_TAG_END) {
                $tag = $this->currentScope?->tag;
                $this->currentScope?->addChild($token);

                if ($tag && is_void_tag($tag)) {
                    $this->closeCurrentScope();
                }
            } elseif ($token->type === TokenType::CLOSING_TAG || $token->type === TokenType::SELF_CLOSING_TAG_END) {
                $this->currentScope?->setCLosingToken($token);
                $this->closeCurrentScope();
            } else {
                $this->currentScope?->addChild($token);
            }
        }

        return $ast;
    }

    private function openScope(Token $token): void
    {
        $this->scope[] = $token;
    }

    private function closeCurrentScope(): void
    {
        array_pop($this->scope);
    }
}