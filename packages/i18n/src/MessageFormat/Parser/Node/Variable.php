<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node;

final readonly class Variable implements Node
{
    public function __construct(
        public Identifier $name,
    ) {}
}
