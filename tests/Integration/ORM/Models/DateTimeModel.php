<?php

namespace Tests\Tempest\Integration\ORM\Models;

use DateTime as NativeDateTime;
use Tempest\Database\PrimaryKey;
use Tempest\DateTime\DateTime;

final class DateTimeModel
{
    public function __construct(
        public PrimaryKey $id,
        public NativeDateTime $phpDateTime,
        public DateTime $tempestDateTime,
    ) {}
}
