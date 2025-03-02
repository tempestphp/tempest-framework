<?php

declare(strict_types=1);

namespace Tempest\Support\Str;

use Tempest\Support\Conditions\HasConditions;
use Tempest\Support\Html\HtmlString;

/**
 * Represents a mutable, manipulable string.
 */
final class MutableString implements StringInterface
{
    use HasConditions;
    use ManipulatesString;

    /**
     * Converts this instance to an {@see ImmutableString} instance.
     */
    public function toImmutableString(): ImmutableString
    {
        return new ImmutableString($this->value);
    }

    /**
     * Converts the instance to a {@see \Tempest\Support\Html\HtmlString}.
     */
    public function toHtmlString(): HtmlString
    {
        return new HtmlString($this->value);
    }
}
