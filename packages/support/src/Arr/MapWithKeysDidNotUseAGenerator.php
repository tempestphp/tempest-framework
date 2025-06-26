<?php

declare(strict_types=1);

namespace Tempest\Support\Arr;

use Exception;

final class MapWithKeysDidNotUseAGenerator extends Exception
{
    public function __construct()
    {
        parent::__construct('Invalid usage of mapWithKeys, the callback must return a generator: `fn (mixed $value, mixed $key) => yield $key => $value`');
    }
}
