<?php

declare(strict_types=1);

namespace Tempest\Interfaces;

interface Caster
{
    public function cast(mixed $input): mixed;
}
