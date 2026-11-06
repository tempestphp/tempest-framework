<?php

namespace Tempest\Upgrade\Tests\Tempest322\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Lifetime;
use UnitEnum;

final class UpdatedContainer implements Container
{
    public function singleton(string $className, mixed $definition, string|UnitEnum|null $tag = null, ?Lifetime $lifetime = null): self
    {
        return $this;
    }
}

final class OtherContainer
{
    public function singleton(string $className, mixed $definition, string|UnitEnum|null $tag = null): self
    {
        return $this;
    }
}
