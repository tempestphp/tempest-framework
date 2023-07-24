<?php

declare(strict_types=1);

namespace Tempest\Interface;

interface Initializer
{
    public function initialize(string $className, Container $container): object;
}
