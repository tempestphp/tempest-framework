<?php

namespace Tempest\Http;

use Generator;

interface DataProvider
{
    public function provide(): Generator;
}