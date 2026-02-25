<?php

namespace Tempest\Intl\MessageFormat\Parser\Node\Markup;

use Tempest\Intl\MessageFormat\Parser\Node\Expression\Attribute;
use Tempest\Intl\MessageFormat\Parser\Node\Expression\Option;
use Tempest\Intl\MessageFormat\Parser\Node\Identifier;
use Tempest\Intl\MessageFormat\Parser\Node\Pattern\Placeholder;

final readonly class Markup implements Placeholder
{
    /**
     * @param Option[] $options
     * @param Attribute[] $attributes
     */
    public function __construct(
        public MarkupType $type,
        public Identifier $identifier,
        public array $options,
        public array $attributes,
    ) {}
}
