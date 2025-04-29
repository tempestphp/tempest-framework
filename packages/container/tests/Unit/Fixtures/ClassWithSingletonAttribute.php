<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Unit\Fixtures;

use Tempest\Container\Singleton;

#[Singleton]
final class ClassWithSingletonAttribute
{
    public bool $flag = false;
}
