<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use DateTimeImmutable;
use Tempest\DateTime\DateTime;
use Tempest\Validation\Rules\HasDateTimeFormat;

final readonly class ObjectThatShouldUseCasters
{
    public function __construct(
        public string $name,
        #[HasDateTimeFormat('Y-m-d')]
        public DateTimeImmutable $nativeDate,
        #[HasDateTimeFormat('yyyy-MM-dd')]
        public DateTime $date,
        public EnumToCast $enum,
    ) {}
}
