<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\TempestConsoleLanguage\Injections;

use Tempest\Console\Highlight\ConsoleTokenType;
use Tempest\Console\Highlight\IsTagInjection;
use Tempest\Highlight\Injection;

final readonly class EmphasizeInjection implements Injection
{
    use IsTagInjection;

    public function getTag(): string
    {
        return 'em';
    }

    public function getTokenType(): ConsoleTokenType
    {
        return ConsoleTokenType::EM;
    }
}
