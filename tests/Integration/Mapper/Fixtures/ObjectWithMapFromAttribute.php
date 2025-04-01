<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\MapFrom;

final readonly class ObjectWithMapFromAttribute
{
    public function __construct(
        #[MapFrom('name')]
        public string $fullName,
    ) {}
}
