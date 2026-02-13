<?php

declare(strict_types=1);

namespace Tempest\Console\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Console\CompletionRuntime;

/**
 * @internal
 */
final class CompletionRuntimeTest extends TestCase
{
    #[Test]
    #[DataProvider('supportedPlatformDataProvider')]
    public function isSupportedPlatform(string $osFamily, bool $expected): void
    {
        $this->assertSame($expected, CompletionRuntime::isSupportedPlatform($osFamily));
    }

    public static function supportedPlatformDataProvider(): array
    {
        return [
            'linux' => ['Linux', true],
            'darwin' => ['Darwin', true],
            'windows' => ['Windows', false],
        ];
    }

    #[Test]
    public function getUnsupportedPlatformMessage(): void
    {
        $this->assertStringContainsString('Windows', CompletionRuntime::getUnsupportedPlatformMessage());
    }

    #[Test]
    public function getHelperBinaryAssetFilename(): void
    {
        $this->assertMatchesRegularExpression(
            '/^tempest-complete_[a-z0-9]+_[a-z0-9_]+$/',
            CompletionRuntime::getHelperBinaryAssetFilename(),
        );
    }

    #[Test]
    public function getHelperBinaryReleaseTag_throws_for_dev_versions(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tagged releases');

        CompletionRuntime::getHelperBinaryReleaseTag();
    }
}
