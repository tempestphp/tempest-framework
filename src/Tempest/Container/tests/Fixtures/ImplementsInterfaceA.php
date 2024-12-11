<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final class ImplementsInterfaceA implements InterfaceA
{
    public function __invoke(): void
    {
    }
}
