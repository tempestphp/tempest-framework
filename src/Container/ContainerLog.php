<?php

declare(strict_types=1);

namespace Tempest\Container;

interface ContainerLog
{
    /**
     * @return \Tempest\Container\Context[]
     */
    public function getStack(): array;

    public function startResolving(): self;

    public function addContext(Context $context): self;

    public function addDependency(Dependency $dependency): self;

    public function currentContext(): Context;

    public function currentDependency(): ?Dependency;

    public function getOrigin(): string;
}
