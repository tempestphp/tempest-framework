<?php

declare(strict_types=1);

namespace Tempest\Support\Str;

use JsonSerializable;
use Stringable;

/**
 * @internal This interface is not meant to be used in userland.
 */
interface StringInterface extends Stringable, JsonSerializable
{
}
