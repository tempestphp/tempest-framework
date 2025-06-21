<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node;

final class Variable implements Node
{
    public function __construct(
        public readonly Identifier $name,
    ) {}
}
