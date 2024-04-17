<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\Injections;

use Tempest\Console\Highlight\IsTagInjection;
use Tempest\Highlight\Injection;
use Tempest\Highlight\Themes\TerminalStyle;

final readonly class QuestionInjection implements Injection
{
    use IsTagInjection;

    public function getTag(): string
    {
        return 'question';
    }

    public function style(string $content): string
    {
        return TerminalStyle::BG_BLUE(" {$content} ");
    }
}
