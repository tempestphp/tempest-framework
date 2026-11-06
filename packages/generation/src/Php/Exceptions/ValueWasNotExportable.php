<?php

declare(strict_types=1);

namespace Tempest\Generation\Php\Exceptions;

use Exception;

final class ValueWasNotExportable extends Exception implements FileGenerationException
{
    public function __construct(mixed $value)
    {
        parent::__construct(sprintf('A value of type "%s" cannot be exported as PHP code. Wrap the expression in a `Literal` instead.', get_debug_type($value)));
    }
}
