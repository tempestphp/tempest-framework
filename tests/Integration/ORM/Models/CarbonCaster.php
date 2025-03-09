<?php

namespace Tests\Tempest\Integration\ORM\Models;

use Carbon\Carbon;
use Tempest\Mapper\Caster;
use Tempest\Mapper\Exceptions\CannotSerializeValue;

final readonly class CarbonCaster implements Caster
{
    public function cast(mixed $input): mixed
    {
        return Carbon::parse($input);
    }

    public function serialize(mixed $input): string
    {
        if (! $input instanceof Carbon) {
            throw new CannotSerializeValue(Carbon::class);
        }

        return $input->toIso8601String();
    }
}