<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Expression;

use Tempest\Internationalization\MessageFormat\Parser\Node\Identifier;
use Tempest\Internationalization\MessageFormat\Parser\Node\Node;

final class FunctionCall implements Node
{
    /**
     * @param (Option)[] $options
     */
    public function __construct(
        public readonly Identifier $identifier,
        public readonly array $options,
    ) {}
}
