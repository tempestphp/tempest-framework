<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\Attributes\MapFrom;

final class ObjectWithMapFromAttribute
{
    public function __construct(
        #[MapFrom('name')]
        public readonly string $fullName
    ) {}
}