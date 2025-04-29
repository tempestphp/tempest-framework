<?php

declare(strict_types=1);

namespace Tempest\Generation;

use Exception;

final class GenerationException extends Exception
{
    public static function needsNamespace(): self
    {
        return new self('A namespace is required to generate a class');
    }
}
