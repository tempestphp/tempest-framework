<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Tables;

use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Database\Migrations\Migration;
use Tempest\Database\Tables\PluralizedSnakeCaseStrategy;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class PluralizedSnakeCaseStrategyTest extends FrameworkIntegrationTestCase
{
    #[TestWith([Migration::class, 'migrations'])]
    #[TestWith(['App\\Models\\PersonalAccessToken', 'personal_access_tokens'])]
    #[TestWith(['App\\Models\\Aircraft', 'aircraft'])] // does not take a "s" in plural form
    public function test_strategy(string $actual, string $expected): void
    {
        $this->assertSame($expected, (new PluralizedSnakeCaseStrategy())->getName($actual));
    }
}
