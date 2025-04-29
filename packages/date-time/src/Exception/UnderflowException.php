<?php

declare(strict_types=1);

namespace Tempest\DateTime\Exception;

use UnderflowException as PhpUnderflowException;

final class UnderflowException extends PhpUnderflowException implements DateTimeException
{
}
