<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Models;

use Carbon\Carbon;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;
use Tempest\Mapper\CastWith;
use Tempest\Mapper\SerializeWith;

final class CarbonModel
{
    use IsDatabaseModel;

    public PrimaryKey $id;

    public function __construct(
        public Carbon $createdAt,
    ) {}
}
