<?php

namespace Tempest\Interfaces;

interface Initializer
{
    public function initialize(string $className, Container $container): object;
}
