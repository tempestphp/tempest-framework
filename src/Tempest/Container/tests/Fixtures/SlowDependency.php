<?php
declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final class SlowDependency
{
    private static int $counter = 0;
    public readonly string $value;
    public function __construct(float $delay = 0.1)
    {
        usleep(intval($delay * 1000000));
        $this->value = 'value' . ++self::$counter;
    }
}
