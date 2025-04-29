<?php

declare(strict_types=1);

namespace Tempest\Router;

use Generator;

interface DataProvider
{
    public function provide(): Generator;
}
