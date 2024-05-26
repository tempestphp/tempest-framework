<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\LogLanguage;

use Tempest\Console\Highlight\LogLanguage\Patterns\LogNamePattern;
use Tempest\Console\Highlight\LogLanguage\Patterns\LogTimestampPattern;
use Tempest\Highlight\Language;

final readonly class LogLanguage implements Language
{
    public function getName(): string
    {
        return 'log';
    }

    public function getAliases(): array
    {
        return [];
    }

    public function getInjections(): array
    {
        return [];
    }

    public function getPatterns(): array
    {
        return [
            new LogTimestampPattern(),
            new LogNamePattern(),
        ];
    }
}
