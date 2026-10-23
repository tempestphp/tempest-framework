<?php

namespace Tempest\Upgrade\Tests\Tempest321\Fixtures;

use Tempest\Container\Container as TempestContainer;
use UnitEnum;

final class CustomContainer implements TempestContainer
{
    public function singleton(string $className, mixed $definition, string|UnitEnum|null $tag = null): self
    {
        return $this;
    }
}
