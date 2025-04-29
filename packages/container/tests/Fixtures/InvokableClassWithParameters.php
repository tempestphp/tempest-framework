<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final class InvokableClassWithParameters
{
    public function __invoke(string $param): string
    {
        return $param;
    }
}
