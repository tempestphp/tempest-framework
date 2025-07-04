<?php

namespace Tempest\Database\Config;

final class SeederConfig
{
    public function __construct(
        /** @var array<array-key, class-string<\Tempest\Database\DatabaseSeeder>> */
        public array $seeders = [],
    ) {}
}
