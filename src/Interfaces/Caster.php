<?php

namespace Tempest\Interfaces;

interface Caster
{
    public function cast(mixed $input): mixed;
}