<?php

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;
use Tempest\Mapper\Exceptions\ValueCouldNotBeCast;
use Tempest\Mapper\MapperConfig;
use Tempest\Support\Arr;
use Tempest\Support\Json;

use function Tempest\map;

final readonly class DtoCaster implements Caster
{
    public function __construct(
        private MapperConfig $mapperConfig,
    ) {}

    public function cast(mixed $input): mixed
    {
        if (! Json\is_valid($input)) {
            throw new ValueCouldNotBeCast('json string');
        }

        ['type' => $type, 'data' => $data] = Json\decode($input);

        $class = Arr\find_key($this->mapperConfig->serializationMap, $type) ?: $type;

        return map($data)->to($class);
    }
}
