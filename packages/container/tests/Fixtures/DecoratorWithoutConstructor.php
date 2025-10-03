<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Decorates;

#[Decorates(DecoratedInterface::class)]
class DecoratorWithoutConstructor implements DecoratedInterface
{
}
