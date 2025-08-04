<?php

namespace Tests\Tempest\Integration\ORM\Models;

use DateTimeImmutable;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;

final class CasterModel
{
    use IsDatabaseModel;

    public PrimaryKey $id;

    public function __construct(
        public DateTimeImmutable $date,
        public array $array_prop,
        public CasterEnum $enum_prop,
    ) {}
}
