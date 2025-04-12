<?php

namespace Tempest\Storage;

use Exception;

final class MissingAdapterException extends Exception implements StorageException
{
    public function __construct(string $missing)
    {
        parent::__construct(
            message: sprintf('The `%s` adapter is missing.', $missing),
        );
    }
}
