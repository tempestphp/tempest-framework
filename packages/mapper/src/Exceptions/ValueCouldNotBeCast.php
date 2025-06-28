<?php

declare(strict_types=1);

namespace Tempest\Mapper\Exceptions;

use Exception;

final class ValueCouldNotBeCast extends Exception
{
    public function __construct(string $expectedType)
    {
        parent::__construct('Could not cast value, input should be of type ' . $expectedType);
    }
}
