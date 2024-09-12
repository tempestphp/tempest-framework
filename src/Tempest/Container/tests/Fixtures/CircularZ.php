<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final readonly class CircularZ
{
    public function __construct(
        public CircularA $a,
    ) {
    }
}
