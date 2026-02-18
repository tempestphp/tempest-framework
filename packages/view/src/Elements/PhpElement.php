<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\Parser\Token;
use Tempest\View\WithToken;

final class PhpElement implements Element, WithToken
{
    use IsElement;

    public function __construct(
        public readonly Token $token,
        private readonly string $content,
    ) {}

    public function compile(): string
    {
        return $this->content;
    }

    public function getImports(): array
    {
        preg_match_all('/^\s*use .*;/m', $this->content, $matches);

        return $matches[0];
    }
}
