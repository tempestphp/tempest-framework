<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Fixtures;

final readonly class Name
{
    public function __construct(
        public string $first,
        public string $last,
    ) {}
}
