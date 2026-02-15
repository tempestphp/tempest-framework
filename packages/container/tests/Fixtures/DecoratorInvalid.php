<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Decorates;

#[Decorates(DecoratedInterface::class)]
final class DecoratorInvalid
{
    public function __construct(
        public DecoratedInterface $decorated,
    ) {}
}
