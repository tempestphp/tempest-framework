<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Discovery;

use Tempest\Core\DoNotDiscover;
use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;

#[DoNotDiscover]
final class HiddenMigration implements DatabaseMigration
{
    public function getName(): string
    {
        return 'hidden-migration';
    }

    public function up(): ?QueryStatement
    {
        return null;
    }

    public function down(): ?QueryStatement
    {
        return null;
    }
}
