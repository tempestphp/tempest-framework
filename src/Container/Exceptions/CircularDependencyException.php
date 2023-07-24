<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;

final class CircularDependencyException extends Exception
{
    public function __construct(string $item)
    {
        parent::__construct("Circular dependency on {$item}!");
    }
}
