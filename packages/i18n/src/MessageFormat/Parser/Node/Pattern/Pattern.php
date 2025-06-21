<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Pattern;

use Tempest\Internationalization\MessageFormat\Parser\Node\Node;

final class Pattern implements Node
{
    /**
     * @param (Text|Placeholder|QuotedPattern)[] $elements
     */
    public function __construct(
        public readonly array $elements,
    ) {}
}
