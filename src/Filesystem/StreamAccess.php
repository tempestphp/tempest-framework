<?php

namespace Tempest\Filesystem;

/**
 * The stream access determines what permissions (read/write) a stream has
 * once the file has been opened. The stream implementation is
 * responsible for enforcing this.
 *
 * This implementation is inspired by the approach in C#.
 *
 * @see https://learn.microsoft.com/en-us/dotnet/api/system.io.fileaccess
 */
enum StreamAccess
{
    case READ;
    case WRITE;
    case READ_WRITE;

    public function canRead(): bool
    {
        return match ($this) {
            self::READ, self::READ_WRITE => true,
            default => false,
        };
    }

    public function canWrite(): bool
    {
        return match($this) {
            self::WRITE, self::READ_WRITE => true,
            default => false,
        };
    }
}