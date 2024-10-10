<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Database\Connections\SQLiteConnection;
use Tempest\Database\DatabaseConfig;
use Tempest\Database\Tables\PascalCaseStrategy;
use Tempest\Database\Tables\PluralizedSnakeCaseStrategy;
use Tests\Tempest\Fixtures\Models\MultiWordModel;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class DatabaseConfigTest extends FrameworkIntegrationTestCase
{
    #[TestWith([PascalCaseStrategy::class, 'MultiWordModel'])]
    #[TestWith([PluralizedSnakeCaseStrategy::class, 'multi_word_models'])]
    public function test_strategy_is_taken_into_account(string $strategy, string $expected): void
    {
        $this->container->config(new DatabaseConfig(
            connection: new SQLiteConnection(
                path: __DIR__ . '/../database.sqlite',
                namingStrategy: new $strategy()
            )
        ));

        $this->assertSame($expected, MultiWordModel::table()->tableName);
    }
}
