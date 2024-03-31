<?php

declare(strict_types=1);

namespace Tempest\Mapper;

final class MapperConfig
{
    public function __construct(
        public array $mappers = []
    ) {

    }

    public function addMapper($mapper): self
    {
        $this->mappers[] = $mapper;

        return $this;
    }
}
