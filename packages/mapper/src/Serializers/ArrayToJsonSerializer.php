<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use Tempest\Support\Arr\ArrayInterface;
use Tempest\Support\Json;

final class ArrayToJsonSerializer implements Serializer
{
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
