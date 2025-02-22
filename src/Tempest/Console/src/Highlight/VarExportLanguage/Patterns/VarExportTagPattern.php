<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\VarExportLanguage\Patterns;

use Tempest\Console\Highlight\ConsoleTokenType;
use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\Tokens\TokenType;

final readonly class VarExportTagPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^(?<match>.*?)\s/';
    }

    public function getTokenType(): TokenType
    {
        return ConsoleTokenType::STRONG;
    }
}
