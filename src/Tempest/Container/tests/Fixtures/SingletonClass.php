<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

class SingletonClass
{
    public static int $count = 0;

    public function __construct()
    {
        self::$count += 1;
    }
}
