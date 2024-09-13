<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\LogLanguage\Patterns;

use Tempest\Console\Highlight\ConsoleTokenType;
use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\Tokens\TokenType;

final readonly class LogTimestampPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^(?<match>\[.*?\])/';
    }

    public function getTokenType(): TokenType
    {
        return ConsoleTokenType::EM;
    }
}
