<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Decorates;

#[Decorates(DecoratedInterface::class)]
class DecoratorClass implements DecoratedInterface
{
    public function __construct(
        public DecoratedInterface $decorated,
    ) {}
}
