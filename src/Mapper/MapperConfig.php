<?php

declare(strict_types=1);

namespace Tempest\Mapper;

final class MapperConfig
{
    /**
     * @param Mapper[] $mappers
     */
    public function __construct(
        public array $mappers = []
    ) {

    }

    public function addMapper(Mapper $mapper): self
    {
        $this->mappers[] = $mapper;

        return $this;
    }
}
