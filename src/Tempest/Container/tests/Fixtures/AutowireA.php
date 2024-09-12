<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final readonly class AutowireA
{
    public function __construct(public AutowireB $b)
    {
    }
}
