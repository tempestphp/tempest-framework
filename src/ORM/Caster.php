<?php

declare(strict_types=1);

namespace Tempest\ORM;

interface Caster
{
    public function cast(mixed $input): mixed;
}
