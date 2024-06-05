<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

final readonly class AutowireA
{
    public function __construct(public AutowireB $b)
    {
    }
}
