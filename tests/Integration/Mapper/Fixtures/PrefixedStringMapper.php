<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Discovery\SkipDiscovery;
use Tempest\Mapper\Mapper;

#[SkipDiscovery]
final readonly class PrefixedStringMapper implements Mapper
{
    public function __construct(
        private MapperPrefix $prefix,
    ) {}

    public function canMap(mixed $from, mixed $to): bool
    {
        return $to === PrefixedString::class;
    }

    public function map(mixed $from, mixed $to): PrefixedString
    {
        return new PrefixedString($this->prefix->value . $from);
    }
}
