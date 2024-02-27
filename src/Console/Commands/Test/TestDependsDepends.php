<?php

declare(strict_types=1);

namespace Tempest\Console\Commands\Test;

use DateTime;

class TestDependsDepends
{
    public function __construct(string|int|DateTime|Test $testing)
    {
    }
}
