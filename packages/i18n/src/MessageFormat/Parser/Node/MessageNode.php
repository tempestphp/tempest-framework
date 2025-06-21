<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node;

use Tempest\Internationalization\MessageFormat\Parser\Node\Pattern\Pattern;

abstract class MessageNode implements Node
{
    public function __construct(
        public readonly Pattern $pattern,
    ) {}
}
