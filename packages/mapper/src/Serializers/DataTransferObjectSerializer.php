<?php

namespace Tempest\Mapper\Serializers;

use BackedEnum;
use JsonSerializable;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\MapperConfig;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Arr;
use Tempest\Support\Json;
use UnitEnum;

final readonly class DataTransferObjectSerializer implements Serializer
{
    public function __construct(
        private MapperConfig $mapperConfig,
    ) {}

    public static function for(): false
    {
        return false;
    }

    public function serialize(mixed $input): array|string
    {
        // Support top-level arrays
        if (is_array($input)) {
            return Json\encode($this->serializeWithType($input));
        }

        if (! is_object($input)) {
            throw new ValueCouldNotBeSerialized('object or array');
        }

        return Json\encode($this->serializeWithType($input));
    }

    private function serializeWithType(mixed $input): mixed
    {
        if ($input instanceof BackedEnum) {
            return $input->value;
        }

        if ($input instanceof UnitEnum) {
            return $input->name;
        }

        if (is_object($input)) {
            $data = $this->extractObjectData($input);

            foreach ($data as $key => $value) {
                $data[$key] = $this->serializeWithType($value);
            }

            $type = $this->mapperConfig->serializationMap[get_class($input)] ?? get_class($input);

            return [
                'type' => $type,
                'data' => $data,
            ];
        }

        if (is_array($input)) {
            return Arr\map_iterable($input, $this->serializeWithType(...));
        }

        return $input;
    }

    private function extractObjectData(object $input): array
    {
        if ($input instanceof JsonSerializable) {
            return $input->jsonSerialize();
        }

        return Arr\map_with_keys(
            array: new ClassReflector($input)->getPublicProperties(),
            map: fn (PropertyReflector $property) => yield $property->getName() => $property->getValue($input),
        );
    }
}
