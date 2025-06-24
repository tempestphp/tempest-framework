<?php

namespace Tempest\Intl\MessageFormat\Parser\Node\ComplexBody;

use Tempest\Intl\MessageFormat\Parser\Node\Node;
use Tempest\Intl\MessageFormat\Parser\Node\Pattern\QuotedPattern;

final readonly class Variant implements Node
{
    /**
     * @param Key[] $keys
     */
    public function __construct(
        public array $keys,
        public QuotedPattern $pattern,
    ) {}
}
