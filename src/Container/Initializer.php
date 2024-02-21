<?php

declare(strict_types=1);

namespace Tempest\Container;

interface Initializer
{
    public function initialize(string $className, Container $container): object;
}
