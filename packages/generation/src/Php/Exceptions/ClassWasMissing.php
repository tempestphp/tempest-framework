<?php

declare(strict_types=1);

namespace Tempest\Generation\Php\Exceptions;

use Exception;

final class ClassWasMissing extends Exception implements FileGenerationException
{
    public function __construct(?string $name = null)
    {
        parent::__construct(match ($name) {
            null => 'The PHP code does not declare a class.',
            default => sprintf('The PHP code does not declare a class named "%s".', $name),
        });
    }
}
