<?php

namespace Tempest\Router\Exceptions;

use Exception;
use Tempest\Reflection\MethodReflector;
use Tempest\Reflection\ParameterReflector;

final class EnumRouteValueWasInvalid extends Exception
{
    public function __construct(
        private(set) readonly MethodReflector $handler,
        private(set) readonly ParameterReflector $parameter,
    ) {}
}
