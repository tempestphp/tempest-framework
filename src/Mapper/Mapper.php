<?php

declare(strict_types=1);

namespace Tempest\Mapper;

interface Mapper
{
    public function canMap(mixed $from, mixed $to): bool;

    /**
     * @template ClassName of object
     * @param ClassName|class-string<ClassName> $to
     * @param mixed $from
     * @return ClassName[]|ClassName|mixed
     */
    public function map(mixed $from, mixed $to): mixed;
}
