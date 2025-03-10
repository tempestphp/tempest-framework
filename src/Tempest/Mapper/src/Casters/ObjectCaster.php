<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;
use Tempest\Mapper\Mappers\ArrayToObjectMapper;
use Tempest\Mapper\Mappers\ObjectToJsonMapper;
use Tempest\Reflection\TypeReflector;
use function Tempest\map;

final readonly class ObjectCaster implements Caster
{
    public function __construct(
        private TypeReflector $type,
    ) {
    }

    public function cast(mixed $input): mixed
    {
        return map($input)->with(ArrayToObjectMapper::class)->to($this->type->getName());
    }

    public function serialize(mixed $input): string
    {
        return map($input)->with(ObjectToJsonMapper::class)->do();
    }
}
