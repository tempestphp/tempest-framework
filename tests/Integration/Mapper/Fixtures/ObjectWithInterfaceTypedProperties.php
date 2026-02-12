<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

final class ObjectWithInterfaceTypedProperties
{
    public function __construct(
        public InterfaceWithCastWith $castable,
        public InterfaceWithSerializeWith $serializable,
    ) {}
}
