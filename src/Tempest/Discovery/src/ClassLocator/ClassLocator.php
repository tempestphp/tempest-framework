<?php

namespace Tempest\Discovery\ClassLocator;

interface ClassLocator
{
    /**
     * @return array<array-key, class-string>
     */
    public function getClasses(): array;
}