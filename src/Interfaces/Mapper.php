<?php

declare(strict_types=1);

namespace Tempest\Interfaces;

interface Mapper
{
    public function canMap(object|string $objectOrClass, mixed $data): bool;

    /**
     * @template ClassName
     * @param ClassName|class-string<ClassName> $className
     * @param mixed $data
     * @return ClassName[]|ClassName
     */
    public function map(object|string $objectOrClass, mixed $data): array|object;
}
