<?php

declare(strict_types=1);

namespace Tempest\Console\Completion;

use Throwable;

use function Tempest\Support\Filesystem\read_file;

final readonly class CompletionMetadataFileReader
{
    public function read(string $metadataPath): ?string
    {
        try {
            return read_file($metadataPath);
        } catch (Throwable) {
            return null;
        }
    }
}
