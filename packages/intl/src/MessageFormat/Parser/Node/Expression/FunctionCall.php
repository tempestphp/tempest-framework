<?php

namespace Tempest\Intl\MessageFormat\Parser\Node\Expression;

use Tempest\Intl\MessageFormat\Parser\Node\Identifier;
use Tempest\Intl\MessageFormat\Parser\Node\Node;

final readonly class FunctionCall implements Node
{
    /**
     * @param (Option)[] $options
     */
    public function __construct(
        public Identifier $identifier,
        public array $options,
    ) {}
}
