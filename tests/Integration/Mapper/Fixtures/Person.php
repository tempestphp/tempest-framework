<?php

namespace Tests\Tempest\Integration\Mapper\Fixtures;

final readonly class Person
{
    public function __construct(
        public Name $name,
    ) {}
}