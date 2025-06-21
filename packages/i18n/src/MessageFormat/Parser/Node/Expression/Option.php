<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Expression;

use Tempest\Internationalization\MessageFormat\Parser\Node\Identifier;
use Tempest\Internationalization\MessageFormat\Parser\Node\Literal\Literal;
use Tempest\Internationalization\MessageFormat\Parser\Node\Node;
use Tempest\Internationalization\MessageFormat\Parser\Node\Variable;

final readonly class Option implements Node
{
    public function __construct(
        public Identifier $identifier,
        public Literal|Variable $value,
    ) {}
}
