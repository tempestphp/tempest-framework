<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\Unit\Fixtures\Database;

interface FakeQueryStatement
{
    public function compile(): string;
}
