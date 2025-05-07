<?php

declare(strict_types=1);

namespace Tempest\Support\Html;

use Stringable;
use Tempest\Support\Str\ImmutableString;
use Tempest\Support\Str\ManipulatesString;
use Tempest\Support\Str\MutableString;
use Tempest\Support\Str\StringInterface;

/**
 * Represents an immutable, manipulable string that will not be escaped if injected into a view.
 */
final class HtmlString implements StringInterface
{
    use ManipulatesString;

    /**
     * Creates an HTML tag with the specified optional attributes and content.
     */
    public static function createTag(string $tag, array $attributes = [], ?string $content = null): self
    {
        return create_tag($tag, $attributes, $content);
    }

    /**
     * Converts this instance to a {@see \Tempest\Support\Str\ImmutableString} instance.
     */
    public function toImmutableString(): ImmutableString
    {
        return new ImmutableString($this->value);
    }

    /**
     * Converts this instance to a {@see \Tempest\Support\Str\MutableString} instance.
     */
    public function toMutableString(): MutableString
    {
        return new MutableString($this->value);
    }

    /**
     * Returns a new instance with the specified string,
     * or mutates the instance if this is a `MutableString`.
     */
    protected function createOrModify(Stringable|string $string): self
    {
        return new static($string);
    }
}
