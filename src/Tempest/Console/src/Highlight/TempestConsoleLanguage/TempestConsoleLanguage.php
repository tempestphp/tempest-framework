<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\TempestConsoleLanguage;

use Tempest\Console\Highlight\TempestConsoleLanguage\Injections\DynamicInjection;
use Tempest\Console\Highlight\TempestConsoleLanguage\Injections\EmphasizeInjection;
use Tempest\Console\Highlight\TempestConsoleLanguage\Injections\H1Injection;
use Tempest\Console\Highlight\TempestConsoleLanguage\Injections\H2Injection;
use Tempest\Console\Highlight\TempestConsoleLanguage\Injections\LinkInjection;
use Tempest\Console\Highlight\TempestConsoleLanguage\Injections\StrongInjection;
use Tempest\Console\Highlight\TempestConsoleLanguage\Injections\UnderlineInjection;
use Tempest\Highlight\Language;

final readonly class TempestConsoleLanguage implements Language
{
    public function getName(): string
    {
        return 'console';
    }

    public function getAliases(): array
    {
        return [];
    }

    public function getInjections(): array
    {
        return [
            new LinkInjection(),
            new EmphasizeInjection(),
            new StrongInjection(),
            new UnderlineInjection(),
            new H1Injection(),
            new H2Injection(),
            new DynamicInjection(),
        ];
    }

    public function getPatterns(): array
    {
        return [];
    }
}
