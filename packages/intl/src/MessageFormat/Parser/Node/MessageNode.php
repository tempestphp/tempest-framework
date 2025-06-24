<?php

namespace Tempest\Intl\MessageFormat\Parser\Node;

use Tempest\Intl\MessageFormat\Parser\Node\Pattern\Pattern;

abstract class MessageNode implements Node
{
    public function __construct(
        public readonly Pattern $pattern,
    ) {}
}
