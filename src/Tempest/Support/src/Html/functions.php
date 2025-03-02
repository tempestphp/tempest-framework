<?php

declare(strict_types=1);

namespace Tempest\Support\Html {
    use Stringable;
    use function Tempest\Support\arr;

    /**
     * Determines whether the specified HTML tag is self-closing.
     */
    function is_self_closing_tag(Stringable|string $tag): bool
    {
        return in_array((string) $tag, [
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
     * Creates an HTML tag with the specified optional attributes and content.
     */
    function create_tag(string $tag, array $attributes = [], ?string $content = null): HtmlString
    {
        $attributes = arr($attributes)
            ->filter(fn (mixed $value) => ! in_array($value, [false, null], strict: true))
            ->map(fn (mixed $value, int|string $key) => $value === true ? $key : $key . '="' . $value . '"')
            ->values()
            ->implode(' ')
            ->when(
                condition: fn ($string) => $string->length() !== 0,
                callback: fn ($string) => $string->prepend(' '),
            )
            ->toString();

        if ($content || ! is_self_closing_tag($tag)) {
            return new HtmlString(sprintf('<%s%s>%s</%s>', $tag, $attributes, $content ?? '', $tag));
        }

        return new HtmlString(sprintf('<%s%s />', $tag, $attributes));
    }
}
