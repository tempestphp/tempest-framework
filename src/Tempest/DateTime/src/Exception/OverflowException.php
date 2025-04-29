<?php

declare(strict_types=1);

namespace Tempest\DateTime\Exception;

use OverflowException as PhpOverflowException;

final class OverflowException extends PhpOverflowException implements DateTimeException
{
}
