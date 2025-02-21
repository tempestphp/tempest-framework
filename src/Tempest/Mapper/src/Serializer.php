<?php

namespace Tempest\Mapper;

interface Serializer
{
    public function serialize(mixed $input): string|null;
}