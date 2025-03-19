<?php

declare(strict_types=1);

namespace Tempest\Mapper;

interface Serializer
{
    public function serialize(mixed $input): array|string;
}
