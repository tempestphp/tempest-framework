<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

final readonly class TaggedDependency
{
    public function __construct(public string $name)
    {
    }
}
