<?php

declare(strict_types=1);

namespace Tempest\Container;

interface RequiresClassName
{
    public function setClassName(string $className): void;
}
