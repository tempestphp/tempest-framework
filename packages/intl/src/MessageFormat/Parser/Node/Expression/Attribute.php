<?php

namespace Tempest\Intl\MessageFormat\Parser\Node\Expression;

use Tempest\Intl\MessageFormat\Parser\Node\Identifier;
use Tempest\Intl\MessageFormat\Parser\Node\Literal\Literal;
use Tempest\Intl\MessageFormat\Parser\Node\Node;

final readonly class Attribute implements Node
{
    public function __construct(
        public Identifier $identifier,
        public ?Literal $value,
    ) {}
}
