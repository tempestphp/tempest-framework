<?php

namespace Tests\Tempest\Integration\ORM\Models;

use DateTimeImmutable;
use Tempest\Database\IsDatabaseModel;

final class CasterModel
{
    use IsDatabaseModel;

    public function __construct(
        public DateTimeImmutable $date,
        public array $array_prop,
        public CasterEnum $enum_prop,
    ) {}
}
