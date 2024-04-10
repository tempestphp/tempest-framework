<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\Injections;

use Tempest\Console\ConsoleStyle;
use Tempest\Console\Highlight\IsTagInjection;
use Tempest\Highlight\Injection;

final readonly class UnderlineInjection implements Injection
{
    use IsTagInjection;

    public function getTag(): string
    {
        return 'u';
    }

    public function style(string $content): string
    {
        return ConsoleStyle::UNDERLINE($content);
    }
}
