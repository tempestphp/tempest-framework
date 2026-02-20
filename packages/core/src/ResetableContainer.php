<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Singleton;

#[Singleton]
class ResetableContainer
{
    /**
     * @param class-string<Resetable>[] $resetableClasses
     */
    public function __construct(
        private(set) array $resetableClasses = [],
    ) {}

    /** @param class-string<Resetable> $class */
    public function add(string $class): void
    {
        $this->resetableClasses[] = $class;
    }
}
