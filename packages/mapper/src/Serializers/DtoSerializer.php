<?php

namespace Tempest\Mapper\Serializers;

use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\MapperConfig;
use Tempest\Mapper\Serializer;
use Tempest\Support\Json;

use function Tempest\map;

final readonly class DtoSerializer implements Serializer
{
    public function __construct(
        private MapperConfig $mapperConfig,
    ) {}

    public function serialize(mixed $input): array|string
    {
        if (! is_object($input)) {
            throw new ValueCouldNotBeSerialized('object');
        }

        $data = map($input)->toArray();
        $type = $this->mapperConfig->serializationMap[get_class($input)] ?? get_class($input);

        return Json\encode([
            'type' => $type,
            'data' => $data,
        ]);
    }
}
