<?php

declare(strict_types=1);

namespace Tempest\Generation\Php\Exceptions;

use Exception;

final class MethodWasMissing extends Exception implements FileGenerationException
{
    public function __construct(string $class, string $method)
    {
        parent::__construct(sprintf('The class "%s" does not have a method named "%s".', $class, $method));
    }
}
