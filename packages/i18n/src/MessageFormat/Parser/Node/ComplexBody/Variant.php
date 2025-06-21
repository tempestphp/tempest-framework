<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\ComplexBody;

use Tempest\Internationalization\MessageFormat\Parser\Node\Node;
use Tempest\Internationalization\MessageFormat\Parser\Node\Pattern\QuotedPattern;

final class Variant implements Node
{
    /**
     * @param Key[] $keys
     */
    public function __construct(
        public readonly array $keys,
        public readonly QuotedPattern $pattern,
    ) {}
}
