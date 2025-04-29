<?php

declare(strict_types=1);

namespace Tempest\Support\Json\Exception;

use InvalidArgumentException;
use Tempest\Support\Json\Exception\JsonException;

final class EncodeException extends InvalidArgumentException implements JsonException
{
}
