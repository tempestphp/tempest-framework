<?php

declare(strict_types=1);

namespace Tempest\Mapper\Exceptions;

use Exception;

final class MapperWasMissing extends Exception
{
    public function __construct()
    {
        parent::__construct('Cannot map using `do()` without calling `with()` first: `map()->with()->do()`');
    }
}
