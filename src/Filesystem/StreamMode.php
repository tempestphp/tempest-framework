<?php

namespace Tempest\Filesystem;

/**
 * The stream mode determines how the file is opened, but not what
 * permissions the stream has (read/write) for the file. The
 * stream implementation is responsible for enforcing this.
 *
 * This implementation is inspired by the approach in C#.
 *
 * @see https://learn.microsoft.com/en-us/dotnet/api/system.io.filemode
 */
enum StreamMode
{
    case APPEND;
    case CREATE;
    CASE CREATE_NEW;
    CASE OPEN;
    CASE OPEN_OR_CREATE;
    CASE TRUNCATE;
}