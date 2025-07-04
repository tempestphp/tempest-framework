<?php

namespace Tests\Tempest\Integration\ORM\Models;

use DateTime;
use Tempest\Database\Id;
use Tempest\DateTime\DateTime as TempestDateTime;

final class DateTimeModel
{
    public function __construct(
        public Id $id,
        public DateTime $phpDateTime,
        public TempestDateTime $tempestDateTime,
    ) {}
}