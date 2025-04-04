<?php
declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final readonly class SlowDependency
{
    public string $value;

    public function __construct(float $delay = 0.1, int $counter = 0)
    {
        // usleep apparently is buggy on windows...
        $start = microtime(true);
        while ((microtime(true) - $start) < $delay) {
            usleep(intval($delay * 1000000));
        }

        $this->value = 'value' . $counter;
    }
}
