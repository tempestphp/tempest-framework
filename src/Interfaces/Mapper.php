<?php

declare(strict_types=1);

namespace Tempest\Interfaces;

interface Mapper
{
    /**
     * @template ClassName
     * @param class-string<ClassName> $className
     * @param mixed $data
     * @return ClassName
     */
    public function map(string $className, mixed $data): object;
}
