<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\Injections;

use Tempest\Console\Highlight\IsTagInjection;
use Tempest\Highlight\Injection;
use Tempest\Highlight\Themes\TerminalStyle;

final readonly class H1Injection implements Injection
{
    use IsTagInjection;

    public function getTag(): string
    {
        return 'h1';
    }

    public function style(string $content): string
    {
        return TerminalStyle::BOLD(TerminalStyle::FG_WHITE(TerminalStyle::BG_DARK_BLUE(" {$content} ")));
    }
}
