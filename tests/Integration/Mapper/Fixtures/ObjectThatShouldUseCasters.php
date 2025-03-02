<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tests\Tempest\Integration\Mapper\Fixtures\EnumToCast;
use Tempest\Validation\Rules\DateTimeFormat;
use DateTimeImmutable;

final readonly class ObjectThatShouldUseCasters
{
    public function __construct(
        public string $name,
        #[DateTimeFormat('Y-m-d')]
        public DateTimeImmutable $date,
        public EnumToCast $enum
    ) {
    }
}