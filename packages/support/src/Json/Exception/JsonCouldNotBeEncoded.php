<?php

declare(strict_types=1);

namespace Tempest\Support\Json\Exception;

use InvalidArgumentException;

final class JsonCouldNotBeEncoded extends InvalidArgumentException implements JsonException {}
