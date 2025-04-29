<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Unit\Fixtures;

final class InvokableClassWithDependencies
{
    public function __invoke(string $param, SingletonClass $_singletonClass): string
    {
        return $param;
    }
}
