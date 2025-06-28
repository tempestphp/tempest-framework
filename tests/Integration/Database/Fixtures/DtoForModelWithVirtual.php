<?php

namespace Tests\Tempest\Integration\Database\Fixtures;

final class DtoForModelWithVirtual
{
    public function __construct(
        public string $data,
    ) {}
}
