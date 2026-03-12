<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Renderers;

use Stringable;
use Tempest\Support\Str\ImmutableString;

use function Tempest\root_path;
use function Tempest\Support\str;

final readonly class KeyValueRenderer
{
    public const int MAX_WIDTH = 125;

    public const int MIN_WIDTH = 3;

    public function render(Stringable|string $key, null|Stringable|string $value = null, bool $useAvailableWidth = false): string
    {
        $key = $this->cleanText($key)->append(' ');
        $value = $this->cleanText($value)->when(
            condition: fn ($s) => $s->stripTags()->length() !== 0,
            callback: fn ($s) => $s->prepend(' '),
        );

        $maximumWidth = $useAvailableWidth
            ? $this->getTerminalWidth()
            : self::MAX_WIDTH;

        $dotsWidth = $maximumWidth - $key->stripTags()->length() - $value->stripTags()->length();

        return str()
            ->append($key)
            ->append('<style="fg-gray dim">', str_repeat('.', max(self::MIN_WIDTH, min($dotsWidth, $maximumWidth))), '</style>')
            ->append($value)
            ->toString();
    }

    private function getTerminalWidth(): int
    {
        $width = shell_exec('tput cols');

        if ($width === false) {
            return self::MAX_WIDTH;
        }

        return (int) $width - 5;
    }

    private function cleanText(null|Stringable|string $text): ImmutableString
    {
        $text = new ImmutableString($text)->trim();

        if ($text->length() === 0) {
            return new ImmutableString();
        }

        return $text
            ->replaceRegex('/\[([^]]+)]/', '<em>[$1]</em>')
            ->when(fn ($s) => $s->endsWith(['.', '?', '!', ':']), fn ($s) => $s->replaceAt(-1, 1, ''))
            ->erase(root_path())
            ->trim();
    }
}
