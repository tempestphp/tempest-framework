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
        if (is_array($input)) {
            // Handle top-level arrays
            return Json\encode($this->wrapWithTypeInfo($input));
        }

        if (! is_object($input)) {
            throw new ValueCouldNotBeSerialized('object or array');
        }

        return Json\encode($this->wrapWithTypeInfo($input));
    }

    private function wrapWithTypeInfo(mixed $input): mixed
    {
        if ($input instanceof \BackedEnum) {
            return $input->value;
        }

        if ($input instanceof \UnitEnum) {
            return $input->name;
        }

        if (is_object($input)) {
            $data = $this->extractObjectData($input);

            foreach ($data as $key => $value) {
                $data[$key] = $this->wrapWithTypeInfo($value);
            }

            $type = $this->mapperConfig->serializationMap[get_class($input)] ?? get_class($input);

            return [
                'type' => $type,
                'data' => $data,
            ];
        }

        if (is_array($input)) {
            return array_map([$this, 'wrapWithTypeInfo'], $input);
        }

        return $input;
    }

    private function extractObjectData(object $input): array
    {
        if ($input instanceof \JsonSerializable) {
            return $input->jsonSerialize();
        }

        $data = [];
        $class = new \ReflectionClass($input);

        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $data[$property->getName()] = $property->getValue($input);
        }

        return $data;
    }
}
