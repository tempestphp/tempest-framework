<?php

declare(strict_types=1);

namespace Tempest\Support\Html {
    use Stringable;

    use function Tempest\Support\arr;

    /**
     * Determines whether the specified HTML tag is a void tag.
     * @see https://developer.mozilla.org/en-US/docs/Glossary/Void_element
     */
    function is_void_tag(Stringable|string $tag): bool
    {
        return in_array(
            (string) $tag,
            [
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
            ],
            strict: true,
        );
    }

    /**
     * Determines whether the specified HTML tag is known HTML tag.
     * @see https://developer.mozilla.org/en-US/docs/Glossary/Tag
     */
    function is_html_tag(Stringable|string $tag): bool
    {
        return (
            is_void_tag($tag) ||
            in_array(
                (string) $tag,
                [
                    'a',
                    'abbr',
                    'acronym',
                    'address',
                    'applet',
                    'area',
                    'article',
                    'aside',
                    'audio',
                    'b',
                    'base',
                    'basefont',
                    'bdi',
                    'bdo',
                    'big',
                    'blockquote',
                    'body',
                    'br',
                    'button',
                    'canvas',
                    'caption',
                    'center',
                    'cite',
                    'code',
                    'col',
                    'colgroup',
                    'data',
                    'datalist',
                    'dd',
                    'del',
                    'details',
                    'dfn',
                    'dialog',
                    'dir',
                    'div',
                    'dl',
                    'dt',
                    'em',
                    'embed',
                    'fieldset',
                    'figcaption',
                    'figure',
                    'font',
                    'footer',
                    'form',
                    'frame',
                    'frameset',
                    'h1',
                    'h2',
                    'h3',
                    'h4',
                    'h5',
                    'h6',
                    'head',
                    'header',
                    'hgroup',
                    'hr',
                    'html',
                    'i',
                    'iframe',
                    'img',
                    'input',
                    'ins',
                    'kbd',
                    'label',
                    'legend',
                    'li',
                    'link',
                    'main',
                    'map',
                    'mark',
                    'menu',
                    'meta',
                    'meter',
                    'nav',
                    'noframes',
                    'noscript',
                    'object',
                    'ol',
                    'optgroup',
                    'option',
                    'output',
                    'p',
                    'param',
                    'picture',
                    'pre',
                    'progress',
                    'q',
                    'rp',
                    'rt',
                    'ruby',
                    's',
                    'samp',
                    'script',
                    'search',
                    'section',
                    'select',
                    'small',
                    'source',
                    'span',
                    'strike',
                    'strong',
                    'style',
                    'sub',
                    'summary',
                    'sup',
                    'svg',
                    'table',
                    'tbody',
                    'td',
                    'template',
                    'textarea',
                    'tfoot',
                    'th',
                    'thead',
                    'time',
                    'title',
                    'tr',
                    'track',
                    'tt',
                    'u',
                    'ul',
                    'var',
                    'video',
                    'wbr',
                ],
                strict: true,
            )
        );
    }

    function format_attributes(array $attributes = []): string
    {
        return $attributes = arr($attributes)
            ->filter(fn (mixed $value) => ! in_array($value, [false, null], strict: true))
            ->map(fn (mixed $value, int|string $key) => $value === true ? $key : ($key . '="' . $value . '"'))
            ->values()
            ->implode(' ')
            ->when(
                condition: fn ($string) => $string->length() !== 0,
                callback: fn ($string) => $string->prepend(' '),
            )
            ->toString();
    }

    /**
     * Creates an HTML tag with the specified optional attributes and content.
     */
    function create_tag(string $tag, array $attributes = [], ?string $content = null): HtmlString
    {
        $attributes = namespace\format_attributes($attributes);

        if ($content || ! is_void_tag($tag)) {
            return new HtmlString(sprintf('<%s%s>%s</%s>', $tag, $attributes, $content ?? '', $tag));
        }

        return new HtmlString(sprintf('<%s%s />', $tag, $attributes));
    }
}
