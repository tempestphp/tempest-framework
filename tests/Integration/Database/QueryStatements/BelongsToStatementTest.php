<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\QueryStatements;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\OnDelete;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class BelongsToStatementTest extends FrameworkIntegrationTestCase
{
    public function test_belongs_to_vs_foreign_key(): void
    {
        $belongsToMigration = new class() implements DatabaseMigration {
            private(set) string $name = '0001_test_belongs_to';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('orders')
                    ->primary()
                    ->text('order_number')
                    ->belongsTo('orders.customer_id', 'customers.id', OnDelete::CASCADE);
            }

            public function down(): ?QueryStatement
            {
                return null;
            }
        };

        $foreignKeyMigration = new class() implements DatabaseMigration {
            private(set) string $name = '0002_test_foreign_key';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('invoices')
                    ->primary()
                    ->text('invoice_number')
                    ->integer('customer_id') // Must explicitly create the column
                    ->foreignKey('invoices.customer_id', 'customers.id', OnDelete::CASCADE);
            }

            public function down(): ?QueryStatement
            {
                return null;
            }
        };

        $this->migrate(CreateMigrationsTable::class, $belongsToMigration, $foreignKeyMigration);

        $this->expectNotToPerformAssertions();
    }

    public function test_foreign_key_allows_different_column_names(): void
    {
        $migration = new class() implements DatabaseMigration {
            private(set) string $name = '0003_test_different_column_names';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('products')
                    ->primary()
                    ->text('name')
                    ->integer('category_ref')
                    ->foreignKey('products.category_ref', 'categories.id', OnDelete::CASCADE);
            }

            public function down(): ?QueryStatement
            {
                return null;
            }
        };

        $this->migrate(CreateMigrationsTable::class, $migration);

        $this->expectNotToPerformAssertions();
    }
}
