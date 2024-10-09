<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\QueryStatements;

use Tempest\Database\Migration;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CreateTableStatementTest extends FrameworkIntegrationTestCase
{
    public function test_defaults(): void
    {
        $migration = new class () implements Migration {
            public function getName(): string
            {
                return '0';
            }

            public function up(): QueryStatement|null
            {
                return (new CreateTableStatement('table'))
                    ->text('text', default: 'default')
                    ->char('char', default: 'd')
                    ->varchar('varchar', default: 'default')
                    ->float('float', default: 0.1)
                    ->integer('integer', default: 1)
                    ->date('date', default: '2024-01-01')
                    ->datetime('datetime', default: '2024-01-01 00:00:00')
                    ->boolean('is_active', default: true);
            }

            public function down(): QueryStatement|null
            {
                return null;
            }
        };

        $this->migrate(
            CreateMigrationsTable::class,
            $migration
        );

        // Make sure there are no errors
        $this->assertTrue(true);
    }
}
