<?php

declare(strict_types=1);

namespace Tempest\Support\Str;

use Closure;
use Stringable;
use Tempest\Support\Html\HtmlString;

/**
 * Represents an immutable, manipulable string.
 */
final class ImmutableString implements StringInterface
{
    use ManipulatesString;

    /**
     * Converts this instance to a {@see MutableString} instance.
     */
    public function toMutableString(): MutableString
    {
        return new MutableString($this->value);
    }

    /**
     * Converts the instance to a {@see \Tempest\Support\Html\HtmlString}.
     */
    public function toHtmlString(): HtmlString
    {
        return new HtmlString($this->value);
    }

    /**
     * Applies the given `$callback` if the `$condition` is true.
     *
     * @param mixed|Closure(static): bool $condition
     * @param Closure(static): static $callback
     */
    public function when(mixed $condition, Closure $callback): static
    {
        if ($condition instanceof Closure) {
            $condition = $condition($this);
        }

        if (! $condition) {
            return $this;
        }

        return $callback($this);
    }

    /**
     * Returns a new instance with the specified string,
     * or mutates the instance if this is a `MutableString`.
     */
    protected function createOrModify(Stringable|string $string): static
    {
        return new static($string);
    }
}
