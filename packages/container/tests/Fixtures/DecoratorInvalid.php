<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Decorator;

#[Decorator(DecoratedInterface::class)]
class DecoratorInvalid
{
    public function __construct(
        public DecoratedInterface $decorated,
    ) {}
}
