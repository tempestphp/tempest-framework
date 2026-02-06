<?php

declare(strict_types=1);

namespace Tempest\Support\Filesystem;

/**
 * Represents the type of advisory lock to acquire on a file.
 *
 * Advisory locks are cooperative and only work if all processes accessing
 * the file respect the locking mechanism.
 */
enum LockType: int
{
    /**
     * Shared lock (reader lock).
     *
     * Multiple processes can hold shared locks simultaneously, allowing
     * concurrent reads. Use this when you only need to read from the file
     * and want to prevent writers from modifying it while you're reading.
     */
    case SHARED = LOCK_SH;

    /**
     * Exclusive lock (writer lock).
     *
     * Only one process can hold an exclusive lock at a time, blocking both
     * readers and writers. Use this when you need to modify the file and
     * want to prevent any other access while you're writing.
     */
    case EXCLUSIVE = LOCK_EX;
}
