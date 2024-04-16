<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\Injections;

use Tempest\Console\ConsoleStyle;
use Tempest\Console\Highlight\IsTagInjection;
use Tempest\Highlight\Injection;

final readonly class H1Injection implements Injection
{
    use IsTagInjection;

    public function getTag(): string
    {
        return 'h1';
    }

    public function style(string $content): string
    {
        return ConsoleStyle::BOLD(ConsoleStyle::FG_WHITE(ConsoleStyle::BG_DARK_BLUE(" {$content} ")));
    }
}
