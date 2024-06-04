<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

final readonly class CircularZ
{
    public function __construct(
        public CircularA $a,
    ) {
    }
}
