<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Unit\Fixtures;

final class ImplementsInterfaceA implements InterfaceA
{
    public function __invoke(): void
    {
    }
}
