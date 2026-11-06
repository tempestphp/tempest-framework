<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Container\Fixtures;

use Tempest\Container\Autowire;
use Tempest\Container\Lifetime;
use Tempest\Container\Singleton;

#[Autowire]
#[Singleton(dynamicTags: true, lifetime: Lifetime::REQUEST)]
final class RequestLifetimeSingleton implements RequestLifetimeInterface {}
