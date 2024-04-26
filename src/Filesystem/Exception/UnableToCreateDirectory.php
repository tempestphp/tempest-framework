<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Exception;

use RuntimeException;

class UnableToCreateDirectory extends RuntimeException
{
    public static function atLocation(string $location, string $because): self
    {
        return new self(
            sprintf('Unable to create directory at location: %s. %s', $location, $because)
        );
    }
}
