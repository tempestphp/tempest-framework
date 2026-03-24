<?php

namespace Tempest\Discovery\Tests\Fixtures;

final class DependencyForItemWithClosureSkip
{
    public function __construct(
        public bool $shouldSkip,
    ) {}
}
