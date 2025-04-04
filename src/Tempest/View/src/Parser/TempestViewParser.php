<?php

namespace Tempest\View\Parser;

final class TempestViewParser
{
    private array $scope = [];

    private ?Token $currentScope {
        get => $this->scope[array_key_last($this->scope)] ?? null;
    }

    public function __construct(
        /** @var \Tempest\View\Parser\Token[] $tokens */
        private readonly array $tokens,
    ) {}

    public function parse(): ?Token
    {
        $root = null;

        foreach ($this->tokens as $token) {
            if ($root === null) {
                $root = $token;
            }

            if ($token->type === TokenType::OPEN_TAG_START) {
                $this->currentScope?->addChild($token);
                $this->openScope($token);
            } elseif ($token->type === TokenType::CLOSING_TAG) {
                $this->currentScope?->setCLosingToken($token);
                $this->closeCurrentScope();
            } else {
                $this->currentScope?->addChild($token);
            }
        }

        return $root;
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