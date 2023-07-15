<?php

declare(strict_types=1);

namespace Tempest\Interfaces;

interface Mapper
{
    public function canMap(mixed $data): bool;

    /**
     * @template ClassType
     * @param class-string<ClassType> $className
     * @param mixed $data
     * @return ClassType
     */
    public function map(string $className, mixed $data): object;
}
