<?php

declare(strict_types=1);

namespace Tempest\Mapper;

final class MapperConfig
{
    public function __construct(
        /** @var array<string,array<string,class-string[]>> */
        public array $mappers = [],
        /** @var array<class-string,string> */
        public array $serializationMap = [],
    ) {}

    /**
     * Serialize `$class` using the given `$name`.
     *
     * @param class-string $class
     */
    public function serializeAs(string $class, string $name): self
    {
        $this->serializationMap[$class] = $name;

        return $this;
    }
}
