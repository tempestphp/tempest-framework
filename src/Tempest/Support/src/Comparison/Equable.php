<?php

declare(strict_types=1);

namespace Tempest\Support\Comparison;

/**
 * @template T
 */
interface Equable
{
    /**
     * @param T $other
     */
    public function equals(mixed $other): bool;
}
