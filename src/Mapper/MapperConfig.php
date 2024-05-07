<?php

namespace Tempest\Mapper;

final class MapperConfig
{
    public function __construct(
        /**
         * @template T of \Tempest\Mapper\Mapper
         * @var class-string<T>[] $mappers
         */
        public array $mappers = [],

        /**
         * @template T of \Tempest\Mapper\Caster
         * @var class-string<T>[] $mappers
         */
        public array $casters = [],
    ) {}
}