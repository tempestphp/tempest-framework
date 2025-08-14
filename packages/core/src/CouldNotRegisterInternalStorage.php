<?php

namespace Tempest\Core;

use Tempest\DateTime\Exception\RuntimeException;

final class CouldNotRegisterInternalStorage extends RuntimeException
{
    public static function fileExists(string $path): self
    {
        return new self("Unable to create internal storage directory, as a file with the same name already exists at `{$path}`");
    }

    public static function directoryNotWritable(string $path): self
    {
        return new self("Unable to create internal storage directory because of insufficient user permission on the root directory at `{$path}`");
    }

    public static function noPermission(string $path): self
    {
        return new self("Insufficient user permission to write to internal storage directory as `{$path}`.");
    }
}
