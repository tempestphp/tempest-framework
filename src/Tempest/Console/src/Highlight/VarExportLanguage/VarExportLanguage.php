<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\VarExportLanguage;

use Tempest\Console\Highlight\VarExportLanguage\Patterns\VarExportTagPattern;
use Tempest\Highlight\Language;

final readonly class VarExportLanguage implements Language
{
    public function getName(): string
    {
        return 'varexport';
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
            new VarExportTagPattern(),
        ];
    }
}
