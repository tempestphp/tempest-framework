<?php

namespace Tests\Tempest\Integration\ORM\Models;

use DateTimeImmutable;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class CasterModel implements DatabaseModel
{
    use IsDatabaseModel;

    public function __construct(
        public DateTimeImmutable $date,
        public array $array,
        public CasterEnum $enum,
    ) {}
}
