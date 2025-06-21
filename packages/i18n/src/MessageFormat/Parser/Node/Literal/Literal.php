<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Literal;

use Tempest\Internationalization\MessageFormat\Parser\Node\Key\Key;
use Tempest\Internationalization\MessageFormat\Parser\Node\Node;

abstract class Literal implements Key, Node
{
    public function __construct(
        public readonly string $value,
    ) {}
}
