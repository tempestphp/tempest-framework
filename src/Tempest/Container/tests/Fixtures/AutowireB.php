<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final readonly class AutowireB
{
    public function __construct(public AutowireC $c)
    {
    }
}
