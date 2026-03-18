<?php

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Resettable;

final class ResettableDependency implements Resettable
{
    public static bool $reset = false;

    public function reset(): void
    {
        self::$reset = true;
    }
}
