<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Tables;

use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Database\Migrations\Migration;
use Tempest\Database\Tables\PascalCaseStrategy;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class PascalCaseStrategyTest extends FrameworkIntegrationTestCase
{
    #[TestWith([Migration::class, 'Migration'])]
    #[TestWith(['App\\Models\\PersonalAccessToken', 'PersonalAccessToken'])]
    public function test_strategy(string $actual, string $expected): void
    {
        $this->assertSame($expected, (new PascalCaseStrategy())->getName($actual));
    }
}
