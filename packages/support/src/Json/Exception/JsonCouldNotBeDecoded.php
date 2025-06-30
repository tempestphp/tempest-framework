<?php

declare(strict_types=1);

namespace Tempest\Support\Json\Exception;

use InvalidArgumentException;

final class JsonCouldNotBeDecoded extends InvalidArgumentException implements JsonException
{
}
