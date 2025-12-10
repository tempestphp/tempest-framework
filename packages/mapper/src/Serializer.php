<?php

declare(strict_types=1);

namespace Tempest\Mapper;

interface Serializer
{
    /**
     * Serializes the given input into a string, array, or integer.
     */
    public function serialize(mixed $input): array|string|int;
}
