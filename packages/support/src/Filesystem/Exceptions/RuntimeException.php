<?php

declare(strict_types=1);

namespace Tempest\Support\Filesystem\Exceptions;

use RuntimeException as PhpRuntimeException;

final class RuntimeException extends PhpRuntimeException implements FilesystemException
{
}
