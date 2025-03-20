<?php

namespace Tests\Tempest\Integration\ORM\Models;

use Carbon\Carbon;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializer;

final readonly class CarbonSerializer implements Serializer
{
    public function serialize(mixed $input): string
    {
        if (! $input instanceof Carbon) {
            throw new CannotSerializeValue(Carbon::class);
        }

        return $input->format('Y-m-d H:i:s');
    }
}