<?php

declare(strict_types=1);

namespace Tempest\Mapper;

final class MapperConfig
{
    public function __construct(
        /** @var class-string[] */
        public array $mappers = [],
    ) {}
}
