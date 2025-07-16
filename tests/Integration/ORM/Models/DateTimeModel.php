<?php

namespace Tests\Tempest\Integration\ORM\Models;

use DateTime as NativeDateTime;
use Tempest\Database\Id;
use Tempest\DateTime\DateTime;

final class DateTimeModel
{
    public function __construct(
        public Id $id,
        public NativeDateTime $phpDateTime,
        public DateTime $tempestDateTime,
    ) {}
}
