<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Scoped;

#[Scoped]
final class ClassWithScopedAttribute
{
    public bool $flag = false;
}
