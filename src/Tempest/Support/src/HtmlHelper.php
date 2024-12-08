<?php

declare(strict_types=1);

namespace Tempest\Support;

final class HtmlHelper
{
    public static function createTag(string $tag, array $attributes = [], ?string $content = null): string
    {
        $attributes = self::compileAttributes($attributes);

        if ($content || ! self::isSelfClosing($tag)) {
            return sprintf('<%s%s>%s</%s>', $tag, $attributes, $content ?? '', $tag);
        }

        return sprintf('<%s%s />', $tag, $attributes);
    }

    public static function isSelfClosing(string $tag): bool
    {
        return in_array($tag, [
            'area',
            'base',
            'br',
            'col',
            'embed',
            'hr',
            'img',
            'input',
            'link',
            'meta',
            'param',
            'source',
            'track',
            'wbr',
        ], strict: true);
    }

    /**
     * Compiles an attribute list to a string of `key="value"`.
     * @param array<string,string> $attributes
     */
    private static function compileAttributes(array $attributes): string
    {
        return arr($attributes)
            ->filter(fn (mixed $value) => ! in_array($value, [false, null], strict: true))
            ->map(fn (mixed $value, int|string $key) => $value === true ? $key : $key . '="' . $value . '"')
            ->values()
            ->implode(' ')
            ->when(
                condition: fn ($string) => $string->length() !== 0,
                callback: fn ($string) => $string->prepend(' '),
            )
            ->toString();
    }
}
