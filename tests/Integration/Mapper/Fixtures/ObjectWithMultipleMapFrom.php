<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\MapFrom;

final class ObjectWithMultipleMapFrom
{
    public function __construct(
        #[MapFrom('name', 'first_name')]
        public readonly string $fullName,
    ) {
    }
}
