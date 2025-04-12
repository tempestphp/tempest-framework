<?php

namespace Tempest\Storage;

use Exception;

final class ForbiddenStorageUsageException extends Exception implements StorageException
{
    public function __construct(
        private readonly ?string $tag = null,
    ) {
        parent::__construct(
            message: $tag
                ? "Storage `{$tag}` is being used without a testing fake."
                : 'Storage is being used without a testing fake.',
        );
    }
}
