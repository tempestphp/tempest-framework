<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use Tempest\Mapper\Context;
use Tempest\Mapper\Mapper;

use function Tempest\Mapper\map;

final readonly class ObjectToJsonMapper implements Mapper
{
    public function __construct(
        private string $context = Context::DEFAULT,
    ) {}

    public function canMap(mixed $from, mixed $to): bool
    {
        return false;
    }

    public function map(mixed $from, mixed $to): string
    {
        $array = map($from)
            ->in($this->context)
            ->toArray();

        return map($array)
            ->in($this->context)
            ->toJson();
    }
}
