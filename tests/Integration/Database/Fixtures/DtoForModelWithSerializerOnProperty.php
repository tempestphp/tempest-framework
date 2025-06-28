<?php

namespace Tests\Tempest\Integration\Database\Fixtures;

final class DtoForModelWithSerializerOnProperty
{
    public function __construct(
        public string $data,
    ) {}
}
