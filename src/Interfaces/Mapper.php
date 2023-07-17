<?php

declare(strict_types=1);

namespace Tempest\Interfaces;

interface Mapper
{
    public function canMap(object|string $objectOrClass, mixed $data): bool;

    /**
     * @template ClassName of object
     * @param ClassName|class-string<ClassName> $objectOrClass
     * @param mixed $data
     * @return ClassName[]|ClassName
     */
    public function map(object|string $objectOrClass, mixed $data): array|object;
}
