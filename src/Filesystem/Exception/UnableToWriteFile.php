<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Exception;

use RuntimeException;

final class UnableToWriteFile extends RuntimeException
{
    public static function atLocation(string $location, string $because): self
    {
        return new self(
            sprintf('Unable to write file at location: %s. %s', $location, $because)
        );
    }
}
