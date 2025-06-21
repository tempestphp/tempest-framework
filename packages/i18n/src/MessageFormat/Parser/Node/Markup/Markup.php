<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Markup;

use Tempest\Internationalization\MessageFormat\Parser\Node\Identifier;
use Tempest\Internationalization\MessageFormat\Parser\Node\Pattern\Placeholder;

final readonly class Markup implements Placeholder
{
    /**
     * @param (Option)[] $options
     * @param (Attribute)[] $attributes
     */
    public function __construct(
        public MarkupType $type,
        public Identifier $identifier,
        public array $options,
        public array $attributes,
    ) {}
}
