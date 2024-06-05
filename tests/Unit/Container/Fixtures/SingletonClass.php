<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

class SingletonClass
{
    public static int $count = 0;

    public function __construct()
    {
        self::$count += 1;
    }
}
