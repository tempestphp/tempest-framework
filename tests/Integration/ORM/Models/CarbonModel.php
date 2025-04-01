<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Models;

use Carbon\Carbon;
use Tempest\Database\IsDatabaseModel;
use Tempest\Mapper\CastWith;
use Tempest\Mapper\SerializeWith;

final class CarbonModel
{
    use IsDatabaseModel;

    public function __construct(
        public Carbon $createdAt,
    ) {}
}
