<?php

namespace Tests\Tempest\Integration\ORM\Models;

use Carbon\Carbon;
use Tempest\Mapper\Caster;

final readonly class CarbonCaster implements Caster
{
    public function cast(mixed $input): mixed
    {
        return new Carbon($input);
    }
}