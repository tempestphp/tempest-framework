<?php

namespace Tempest\Intl\MessageFormat\Parser\Node\Literal;

use Tempest\Intl\MessageFormat\Parser\Node\Key\Key;
use Tempest\Intl\MessageFormat\Parser\Node\Node;

abstract class Literal implements Key, Node
{
    public function __construct(
        public readonly string $value,
    ) {}
}
