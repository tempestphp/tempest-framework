<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

use Tempest\Container\Singleton;

#[Singleton]
final class ClassWithSingletonAttribute
{
    public bool $flag = false;
}
