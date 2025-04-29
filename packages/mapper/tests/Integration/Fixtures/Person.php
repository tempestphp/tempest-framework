<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Fixtures;

final readonly class Person
{
    public function __construct(
        public Name $name,
    ) {}
}
