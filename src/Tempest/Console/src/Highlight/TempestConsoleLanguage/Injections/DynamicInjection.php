<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\TempestConsoleLanguage\Injections;

use Tempest\Console\Highlight\ConsoleTokenType;
use Tempest\Console\Highlight\DynamicTokenType;
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Injection;
use Tempest\Highlight\ParsedInjection;

final readonly class DynamicInjection implements Injection
{
    public function getTokenType(): ConsoleTokenType
    {
        return ConsoleTokenType::COMMENT;
    }

    public function parse(string $content, Highlighter $highlighter): ParsedInjection
    {
        $pattern = '/(?<match>\<(?<tag>mod|fg|bg)=(?<mod>[a-zA-Z]+)\>(.|\n)*?\<\/\k<tag>\>)/';

        $result = preg_replace_callback(
            pattern: $pattern,
            callback: function ($matches) use ($highlighter, $pattern) {
                $content = $matches['match'] ?? '';

                if (! $content) {
                    return $matches[0];
                }

                $tag = $matches['tag'];
                $mod = $matches['mod'];
                $token = new DynamicTokenType($tag, $mod);
                $theme = $highlighter->getTheme();

                $result = str_replace(
                    search: $content,
                    replace: str_replace(
                        search: ["<{$tag}={$mod}>", "</{$tag}>"],
                        replace: [$theme->before($token), $theme->after($token)],
                        subject: $content
                    ),
                    subject: $matches[0],
                );

                if (preg_match($pattern, $result)) {
                    return $this->parse($result, $highlighter)->content;
                }

                return $result;
            },
            subject: $content,
        );

        return new ParsedInjection($result ?? $content);
    }
}
