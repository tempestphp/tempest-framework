<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\Unit\Fixtures\Database;

use Tempest\Generation\Tests\Unit\Fixtures\Database\FakeQueryStatement;

interface FakeMigration
{
    public function getName(): string;

    public function up(): ?FakeQueryStatement;
}
