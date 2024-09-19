<?php

declare(strict_types=1);

namespace Tempest\Support {
    function str(string $string = ''): StringHelper
    {
        return new StringHelper($string);
    }
}
