<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\Injections;

use Tempest\Console\Highlight\IsTagInjection;
use Tempest\Highlight\Injection;
use Tempest\Highlight\Themes\TerminalStyle;

final readonly class CommentInjection implements Injection
{
    use IsTagInjection;

    public function getTag(): string
    {
        return 'comment';
    }

    public function style(string $content): string
    {
        $lines = explode(PHP_EOL, $content);

        if (count($lines) > 1) {
            $comment = implode(
                PHP_EOL,
                [
                    '/*',
                    ...array_map(
                        fn (string $line) => " * {$line}",
                        $lines,
                    ),
                    ' */',
                ],
            );
        } else {
            $comment = '/* ' . $lines[0] . ' */';
        }


        $comment = str_replace('/* *', '/*', $comment);

        return TerminalStyle::FG_GRAY($comment);
    }
}
