<?php

declare(strict_types=1);

namespace Tempest\Support\Json\Exception;

use InvalidArgumentException;

final class DecodeException extends InvalidArgumentException implements JsonException
{
}
