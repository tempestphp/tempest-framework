<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Exception;

use RuntimeException;

final class UnableToReadFile extends RuntimeException
{
    public static function fromLocation(string $location, string $because = ''): self
    {
        return new self(
            sprintf('Unable to read file from location: %s. %s', $location, $because)
        );
    }
}
