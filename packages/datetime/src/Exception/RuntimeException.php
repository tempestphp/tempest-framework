<?php

declare(strict_types=1);

namespace Tempest\DateTime\Exception;

use RuntimeException as PhpRuntimeException;

// @phpstan-ignore-next-line
class RuntimeException extends PhpRuntimeException implements DateTimeException
{
}
