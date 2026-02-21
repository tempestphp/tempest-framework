<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Singleton;

#[Singleton]
class ResetableContainer
{
    /**
     * @param class-string<Resetable>[] $resetableClasses
     * @param class-string<ResetableStatic>[] $resetableStaticClasses
     */
    public function __construct(
        private(set) array $resetableClasses = [],
        private(set) array $resetableStaticClasses = [],
    ) {}

    /** @param class-string<Resetable> $class */
    public function add(string $class): void
    {
        $this->resetableClasses[] = $class;
    }

    /** @param class-string<ResetableStatic> $class */
    public function addStatic(string $class): void
    {
        $this->resetableStaticClasses[] = $class;
    }
}
