<?php

declare(strict_types=1);

namespace Tempest\Mapper;

interface Mapper
{
    public function canMap(object|string $to, mixed $from): bool;

    /**
     * @template ClassName of object
     * @param ClassName|class-string<ClassName> $to
     * @param mixed $from
     * @return ClassName[]|ClassName
     */
    public function map(object|string $to, mixed $from): array|object;
}
