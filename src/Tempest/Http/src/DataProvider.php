<?php

declare(strict_types=1);

namespace Tempest\Http;

use Generator;

interface DataProvider
{
    public function provide(): Generator;
}
