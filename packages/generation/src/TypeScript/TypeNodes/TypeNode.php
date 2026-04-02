<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeNodes;

/**
 * Represents a semantic TypeScript type expression.
 */
interface TypeNode
{
    /**
     * References to FQCNs that this type node depends on.
     *
     * @var string[]
     */
    public array $references { get; }
}
