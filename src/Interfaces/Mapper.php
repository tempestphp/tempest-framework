<?php

declare(strict_types=1);

namespace Tempest\Interfaces;

interface Mapper
{
    public function canMap(mixed $data): bool;

    /**
     * @template ClassName
     * @param class-string<ClassName> $className
     * @param mixed $data
     * @return ClassName[]|ClassName
     */
    public function map(string $className, mixed $data): array|object;
}
