<?php

declare(strict_types=1);

namespace Tempest\Filesystem;

use Tempest\Filesystem\Local\LocalDriver;

final class FilesystemFactory
{
    // TODO: We may want to get rid of this,
    // just playing around with syntax.
    public static function local(string $basePath): Filesystem
    {
        // TODO: Implement base path.
        return new Filesystem(new LocalDriver());
    }
}
