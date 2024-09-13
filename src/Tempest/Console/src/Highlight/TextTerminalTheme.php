<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight;

use Tempest\Highlight\TerminalTheme;
use Tempest\Highlight\Tokens\TokenType;

final readonly class TextTerminalTheme implements TerminalTheme
{
    public function before(TokenType $tokenType): string
    {
        return '';
    }

    public function after(TokenType $tokenType): string
    {
        return '';
    }

    public function escape(string $content): string
    {
        return $content;
    }
}
