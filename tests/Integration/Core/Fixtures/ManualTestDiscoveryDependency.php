<?php

namespace Tests\Tempest\Integration\Core\Fixtures;

use Tempest\Container\Singleton;

#[Singleton]
final class ManualTestDiscoveryDependency
{
    public bool $discovered = false;
}