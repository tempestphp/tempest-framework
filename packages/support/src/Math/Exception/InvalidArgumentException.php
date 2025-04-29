<?php

declare(strict_types=1);

namespace Tempest\Support\Math\Exception;

use InvalidArgumentException as PhpInvalidArgumentException;

final class InvalidArgumentException extends PhpInvalidArgumentException implements MathException
{
}
