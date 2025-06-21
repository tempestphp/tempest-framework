<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Expression;

use Tempest\Internationalization\MessageFormat\Parser\Node\Identifier;
use Tempest\Internationalization\MessageFormat\Parser\Node\Literal\Literal;
use Tempest\Internationalization\MessageFormat\Parser\Node\Node;
use Tempest\Internationalization\MessageFormat\Parser\Node\Variable;

final class Option implements Node
{
    public function __construct(
        public readonly Identifier $identifier,
        public readonly Literal|Variable $value,
    ) {}
}
