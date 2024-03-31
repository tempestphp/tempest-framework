<?php

declare(strict_types=1);

namespace Tempest\Validation;

use ReflectionProperty;
use ReflectionParameter;

interface Inferrer
{

    /**
     * @param ReflectionProperty|ReflectionParameter $reflection
     * @param mixed $value
     *
     * @return Rule[]
     */
    public function infer(ReflectionProperty|ReflectionParameter $reflection, mixed $value): array;
}
