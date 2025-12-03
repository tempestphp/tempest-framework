<?php

namespace Tempest\Testing\Tests\Fixtures;

final class Dependency
{
    public function __construct(
        public string $name = 'default',
    ) {}
}
