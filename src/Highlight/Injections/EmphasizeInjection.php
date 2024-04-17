<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\Injections;

use Tempest\Console\Highlight\IsTagInjection;
use Tempest\Highlight\Injection;
use Tempest\Highlight\Themes\TerminalStyle;

final readonly class EmphasizeInjection implements Injection
{
    use IsTagInjection;

    public function getTag(): string
    {
        return 'em';
    }

    public function style(string $content): string
    {
        return TerminalStyle::FG_BLUE($content);
    }
}
