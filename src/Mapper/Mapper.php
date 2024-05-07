<?php

declare(strict_types=1);

namespace Tempest\Mapper;

interface Mapper
{
    public function canMap(mixed $from, object|string $to): bool;

    /**
     * @template ClassName of object
     * @param ClassName|class-string<ClassName> $to
     * @param mixed $from
     * @return ClassName[]|ClassName
     */
    public function map(mixed $from, object|string $to): array|object;
}
