<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight\TempestConsoleLanguage\Injections;

use Tempest\Console\Highlight\ConsoleTokenType;
use Tempest\Console\Highlight\DynamicTokenType;
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Injection;
use Tempest\Highlight\ParsedInjection;
use function Tempest\Support\str;

final readonly class DynamicInjection implements Injection
{
    public function getTokenType(): ConsoleTokenType
    {
        return ConsoleTokenType::COMMENT;
    }

    public function parse(string $content, Highlighter $highlighter): ParsedInjection
    {
        $pattern = '/(?<match>\<style=\"(?<styles>(?:[a-z-]+\s*)+)\"\>(.|\n)*\<\/style\>)/';

        $result = preg_replace_callback(
            pattern: $pattern,
            callback: function ($matches) use ($highlighter, $pattern) {
                $theme = $highlighter->getTheme();
                $content = $matches['match'];
                $styles = $matches['styles'];
                $before = '';
                $after = '';

                foreach (explode(' ', $styles) as $style) {
                    $token = new DynamicTokenType($style);
                    $before .= $theme->before($token);
                    $after .= $theme->after($token);
                }

                $result = str_replace(
                    search: $content,
                    replace: str($content)
                        ->replaceFirst("<style=\"{$styles}\">", $before)
                        ->replaceLast("</style>", $after)
                        ->toString(),
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
