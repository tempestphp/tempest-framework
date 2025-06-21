<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Markup;

use Tempest\Internationalization\MessageFormat\Parser\Node\Identifier;
use Tempest\Internationalization\MessageFormat\Parser\Node\Pattern\Placeholder;

final class Markup implements Placeholder
{
    /**
     * @param (Option)[] $options
     * @param (Attribute)[] $attributes
     */
    public function __construct(
        public readonly MarkupType $type,
        public readonly Identifier $identifier,
        public readonly array $options,
        public readonly array $attributes,
    ) {}
}
