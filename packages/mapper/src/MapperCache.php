<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\Container\Resettable;
use Tempest\Container\Singleton;

/**
 * Caches the mapper instances that were resolved for a given mapping context.
 *
 * Scoped to a container rather than a static. Mappers therefore cannot leak between containers, and
 * resetting keeps workers from holding on to mappers built from dependencies that were unregistered.
 */
#[Singleton]
final class MapperCache implements Resettable
{
    /** @var array<string, \Tempest\Mapper\Mapper[]> */
    private array $mappers = [];

    /**
     * @param callable(): \Tempest\Mapper\Mapper[] $resolve
     * @return \Tempest\Mapper\Mapper[]
     */
    public function resolve(Context $context, callable $resolve): array
    {
        return $this->mappers[$context->name] ??= $resolve();
    }

    public function reset(): void
    {
        $this->mappers = [];
    }
}
