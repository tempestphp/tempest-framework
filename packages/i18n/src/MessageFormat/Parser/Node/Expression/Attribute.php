<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Expression;

use Tempest\Internationalization\MessageFormat\Parser\Node\Identifier;
use Tempest\Internationalization\MessageFormat\Parser\Node\Literal\Literal;
use Tempest\Internationalization\MessageFormat\Parser\Node\Node;

final readonly class Attribute implements Node
{
    public function __construct(
        public Identifier $identifier,
        public ?Literal $value,
    ) {}
}
