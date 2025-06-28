<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use DateTimeImmutable;
use Tempest\DateTime\DateTime;
use Tempest\Validation\Rules\DateTimeFormat;

final readonly class ObjectThatShouldUseCasters
{
    public function __construct(
        public string $name,
        #[DateTimeFormat('Y-m-d')]
        public DateTimeImmutable $nativeDate,
        #[DateTimeFormat('yyyy-MM-dd')]
        public DateTime $date,
        public EnumToCast $enum,
    ) {}
}
