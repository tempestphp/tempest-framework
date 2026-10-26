<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Singleton;

#[Singleton(dynamicTags: true)]
final readonly class DynamicTaggedDependency {}
