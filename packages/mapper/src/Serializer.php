<?php

declare(strict_types=1);

namespace Tempest\Mapper;

interface Serializer
{
    /**
     * Serializes the given input into an array, boolean, float, integer, or string.
     */
    public function serialize(mixed $input): array|bool|float|int|string;
}
