<?php

namespace Tempest\Mapper\Exceptions;

use Exception;

final class MissingMapperException extends Exception
{
    public function __construct()
    {
        parent::__construct('Cannot map using `do()` without calling `with()` first: `map()->with()->do()`');
    }
}
