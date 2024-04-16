<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\Injections;

use Tempest\Console\ConsoleStyle;
use Tempest\Console\Highlight\IsTagInjection;
use Tempest\Highlight\Injection;

final readonly class CommentInjection implements Injection
{
    use IsTagInjection;

    public function getTag(): string
    {
        return 'comment';
    }

    public function style(string $content): string
    {
        $comment = implode(
            PHP_EOL,
            [
                '/*',
                ...array_map(
                    fn (string $line) => "* {$line}",
                    explode(PHP_EOL, $content),
                ),
                '*/',
            ],
        );

        return ConsoleStyle::FG_GRAY($comment);
    }
}
