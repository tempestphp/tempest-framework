<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

final class ConcreteInterfaceValue implements InterfaceWithCastWith, InterfaceWithSerializeWith
{
    public function __construct(
        private string $value,
    ) {}

    public function getValue(): string
    {
        return $this->value;
    }
}
