<?php

namespace Tempest\Container\Discovery\ClassFactory;

interface ClassFactory
{
    /**
     * @param class-string $class
     */
    public function create(string $class): object;
}