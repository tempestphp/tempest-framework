<?php

declare(strict_types=1);

namespace Tempest\Generation\Enums;

/**
 * Defines the type of a stub file.
 * Useful for determining how to handle a file when generating it.
 */
enum StubFileType: string
{
    /**
     * A raw file is any file that is not a PHP class (e.g. a view, a config file, a raw PHP file etc).
     */
    case RAW_FILE = 'raw';

    /**
     * A class file is a PHP file that contains a class.
     */
    case CLASS_FILE = 'class';
}
