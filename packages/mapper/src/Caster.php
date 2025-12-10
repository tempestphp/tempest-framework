<?php

declare(strict_types=1);

namespace Tempest\Mapper;

interface Caster
{
    /**
     * Creates an object or a scalar value from the given input.
     */
    public function cast(mixed $input): mixed;
}
