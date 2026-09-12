<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final class PrefixedString
{
    public function __construct(
        public string $value,
    ) {}
}
