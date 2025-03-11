<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\Fixtures\Database;

interface FakeMigration
{
    public function getName(): string;

    public function up(): ?FakeQueryStatement;
}
