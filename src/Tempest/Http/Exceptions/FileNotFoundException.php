<?php

declare(strict_types=1);

namespace Tempest\Http\Exceptions;

use Exception;

final class FileNotFoundException extends Exception
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf("The file %s does not exist", $path));
    }
}