<?php

declare(strict_types=1);

namespace Tempest\Interface;

interface Caster
{
    public function cast(mixed $input): mixed;
}
