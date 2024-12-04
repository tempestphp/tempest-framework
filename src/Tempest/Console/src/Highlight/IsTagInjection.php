<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight;

use Tempest\Highlight\Highlighter;
use Tempest\Highlight\ParsedInjection;

trait IsTagInjection
{
    abstract public function getTag(): string;

    abstract public function getTokenType(): ConsoleTokenType;

    public function parse(string $content, Highlighter $highlighter): ParsedInjection
    {
        $pattern = $this->getPattern();

        if (! str_starts_with($pattern, '/')) {
            $pattern = "/{$pattern}/";
        }

        $result = preg_replace_callback(
            pattern: $pattern,
            callback: function ($matches) use ($highlighter) {
                $content = $matches['match'] ?? '';

                if (! $content) {
                    return $matches[0];
                }

                $tag = $this->getTag();

                $theme = $highlighter->getTheme();

                return str_replace(
                    search: $content,
                    replace: str_replace(
                        ["<{$tag}>", "</{$tag}>"],
                        [
                            $theme->before($this->getTokenType()),
                            $theme->after($this->getTokenType()),
                        ],
                        $content,
                    ),
                    subject: $matches[0],
                );
            },
            subject: $content,
        );

        return new ParsedInjection($result ?? $content);
    }

    private function getPattern(): string
    {
        $tag = $this->getTag();

        return '(?<match>\<' . $tag . '\>(.|\n)*?\<\/' . $tag . '\>)';
    }
}
