<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Expression;

use Tempest\Internationalization\MessageFormat\Parser\Node\Identifier;
use Tempest\Internationalization\MessageFormat\Parser\Node\Node;

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
