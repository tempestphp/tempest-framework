<?php

namespace Tempest\Support;

use Exception;

final class InvalidMapWithKeysUsage extends Exception
{
    public function __construct()
    {
        parent::__construct('Invalid usage of mapWithKeys, the callback must return a generator: `fn (mixed $value, mixed $key) => yield $key => $value`');
    }
}