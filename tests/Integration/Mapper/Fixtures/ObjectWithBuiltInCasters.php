<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use DateTime;
use DateTimeImmutable;
use Tempest\Validation\Rules\DateTimeFormat;

final class ObjectWithBuiltInCasters
{
    public ?DateTimeImmutable $nullableDateTimeImmutable = null;

    public DateTimeImmutable $dateTimeObject;

    public DateTimeImmutable $dateTimeImmutable;

    public DateTime $dateTime;

    #[DateTimeFormat('d/m/Y H:i:s')]
    public DateTime $dateTimeWithFormat;

    public bool $bool;

    public float $float;

    public int $int;
}
