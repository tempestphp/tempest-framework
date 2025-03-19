<?php

declare(strict_types=1);

namespace Tempest\Mapper;

interface Caster
{
    public function cast(mixed $input): mixed;
}
