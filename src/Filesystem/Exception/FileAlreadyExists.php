<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Exception;

use RuntimeException;

final class FileAlreadyExists extends RuntimeException
{
    public static function atPath(string $path): self
    {
        return new self();
    }
}
