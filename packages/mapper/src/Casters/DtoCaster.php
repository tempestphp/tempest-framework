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
        if (is_string($input) && Json\is_valid($input)) {
            return $this->deserialize(Json\decode($input));
        }

        if (is_array($input)) {
            return $this->deserialize($input);
        }

        if (is_string($input)) {
            throw new ValueCouldNotBeCast('json string');
        }

        return $input;
    }

    private function deserialize(mixed $input): mixed
    {
        if (is_array($input) && isset($input['type'], $input['data'])) {
            $class = Arr\find_key(
                array: $this->mapperConfig->serializationMap,
                value: $input['type'],
            ) ?: $input['type'];

            return map($this->deserialize($input['data']))->to($class);
        }

        if (is_array($input)) {
            return array_map(fn (mixed $value) => $this->deserialize($value), $input);
        }

        return $input;
    }
}
