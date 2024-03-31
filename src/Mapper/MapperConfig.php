<?php

declare(strict_types=1);

namespace Tempest\Mapper;

final class MapperConfig
{
    public function __construct(
        /** @var Mapper[] */
        public array $mappers = []
    ) {

    }

    public function addMapper(Mapper $mapper): self
    {
        $this->mappers[] = $mapper;

        return $this;
    }
}
