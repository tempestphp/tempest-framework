<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use Tempest\Mapper\Context;
use Tempest\Mapper\Mapper;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Json;
use Throwable;

use function Tempest\Mapper\map;

final readonly class JsonToObjectMapper implements Mapper
{
    public function __construct(
        private string $context = Context::DEFAULT,
    ) {}

    public function canMap(mixed $from, mixed $to): bool
    {
        if (! is_string($from)) {
            return false;
        }

        if (! Json\is_valid($from)) {
            return false;
        }

        try {
            $class = new ClassReflector($to);

            return $class->isInstantiable();
        } catch (Throwable) {
            return false;
        }
    }

    public function map(mixed $from, mixed $to): object
    {
        $array = map($from)
            ->in($this->context)
            ->toArray();

        return map($array)
            ->in($this->context)
            ->to($to);
    }
}
