<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;
use Tempest\Mapper\Mappers\ArrayToObjectMapper;
use Tempest\Reflection\TypeReflector;

use function Tempest\map;

final readonly class ObjectCaster implements Caster
{
    public function __construct(
        private TypeReflector $type,
    ) {}

    public function cast(mixed $input): mixed
    {
        // TODO: difference with ArrayToObjectCaster? This can probably be removed after we've added support for #984
        return map($input)->with(ArrayToObjectMapper::class)->to($this->type->getName());
    }
}
