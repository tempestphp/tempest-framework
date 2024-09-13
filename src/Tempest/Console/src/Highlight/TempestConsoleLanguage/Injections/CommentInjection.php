<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\TempestConsoleLanguage\Injections;

use Tempest\Console\Highlight\ConsoleTokenType;
use Tempest\Console\Highlight\IsTagInjection;
use Tempest\Highlight\Injection;

final readonly class CommentInjection implements Injection
{
    use IsTagInjection;

    //    public function parse(string $content, Highlighter $highlighter): ParsedInjection
    //    {
    //        $lines = explode(PHP_EOL, $content);
    //
    //        if (count($lines) > 1) {
    //            $comment = implode(
    //                PHP_EOL,
    //                [
    //                    '/*',
    //                    ...array_map(
    //                        fn (string $line) => " * {$line}",
    //                        $lines,
    //                    ),
    //                    ' */',
    //                ],
    //            );
    //        } else {
    //            $comment = '/* ' . $lines[0] . ' */';
    //        }
    //
    //        $comment = str_replace('/* *', '/*', $comment);
    //
    //        return $comment;
    //
    //        return $this->parseContent($content, $highlighter);
    //    }

    public function getTag(): string
    {
        return 'comment';
    }

    public function getTokenType(): ConsoleTokenType
    {
        return ConsoleTokenType::COMMENT;
    }
}
