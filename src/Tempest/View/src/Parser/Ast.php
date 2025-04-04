<?php

namespace Tempest\View\Parser;

final class Ast
{
    public function __construct(
        private(set) TokenCollection $tokens = new TokenCollection(),
    ) {}

    public function add(Token $token): self
    {
        $this->tokens->add($token);

        return $this;
    }

    public function compile(): string
    {
        return implode('', array_map(
            fn (Token $token) => $token->compile(),
            iterator_to_array($this->tokens),
        ));
    }
}