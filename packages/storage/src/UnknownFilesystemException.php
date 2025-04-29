<?php

namespace Tempest\Storage;

use Exception;

final class UnknownFilesystemException extends Exception implements StorageException
{
    public function __construct(
        public readonly string $filesystem,
    ) {
        parent::__construct(
            message: sprintf('Unknown filesystem `%s`.', $filesystem),
        );
    }
}
