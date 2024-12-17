<?php

declare(strict_types=1);

namespace Tempest\Support;

use Stringable;

final readonly class HtmlString implements Stringable
{
    public function __construct(
        private string $value,
    ) {
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function toStringHelper(): StringHelper
    {
        return new StringHelper($this->value);
    }

    public static function createTag(string $tag, array $attributes = [], ?string $content = null): self
    {
        $attributes = self::compileAttributes($attributes);

        if ($content || ! self::isSelfClosingTag($tag)) {
            return new self(sprintf('<%s%s>%s</%s>', $tag, $attributes, $content ?? '', $tag));
        }

        return new self(sprintf('<%s%s />', $tag, $attributes));
    }

    public static function isSelfClosingTag(string $tag): bool
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
