<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

/**
 * Represents a PHP type resolved to a TypeScript one as a string.
 */
final readonly class ResolvedType
{
    /**
     * @param string $type A resolved TypeScript type.
     * @param null|string $fqcn The PHP FQCN of the original type.
     */
    public function __construct(
        public string $type,
        public ?string $fqcn = null,
    ) {}
}
