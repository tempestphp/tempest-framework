<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight;

use Tempest\Highlight\Tokens\TokenType;

enum ConsoleTokenType implements TokenType
{
    case COMMENT;
    case EM;
    case ERROR;
    case H1;
    case H2;
    case QUESTION;
    case STRONG;
    case SUCCESS;
    case UNDERLINE;
    case HIGHLIGHT;

    public function getValue(): string
    {
        return $this->name;
    }

    public function canContain(TokenType $other): bool
    {
        return false;
    }
}
