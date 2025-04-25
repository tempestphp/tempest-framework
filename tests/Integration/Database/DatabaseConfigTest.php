<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Database\Tables\PascalCaseStrategy;
use Tempest\Database\Tables\PluralizedSnakeCaseStrategy;
use Tests\Tempest\Fixtures\Models\MultiWordModel;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class DatabaseConfigTest extends FrameworkIntegrationTestCase
{
    #[TestWith([PascalCaseStrategy::class, 'MultiWordModel'])]
    #[TestWith([PluralizedSnakeCaseStrategy::class, 'multi_word_models'])]
    public function test_strategy_is_taken_into_account(string $strategy, string $expected): void
    {
        $this->container->config(new SQLiteConfig(
            path: __DIR__ . '/../database.sqlite',
            namingStrategy: new $strategy(),
        ));

        $this->assertSame($expected, new ModelDefinition(MultiWordModel::class)->getTableDefinition()->name);
    }
}
