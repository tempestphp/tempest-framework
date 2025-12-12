<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use Tempest\Core\Priority;
use Tempest\Mapper\DynamicSerializer;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use Tempest\Support\Arr\ArrayInterface;
use Tempest\Support\Json;

#[Priority(Priority::NORMAL)]
final class ArrayToJsonSerializer implements Serializer, DynamicSerializer
{
    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->getName() === 'array';
    }

    public function serialize(mixed $input): string
    {
        if ($input instanceof ArrayInterface) {
            $input = $input->toArray();
        }

        if (! is_array($input)) {
            throw new ValueCouldNotBeSerialized('array');
        }

        return Json\encode($input);
    }
}
