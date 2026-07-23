<?php

declare(strict_types=1);

namespace Tempest\Mcp\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Mcp\ProtocolVersion;

/**
 * @internal
 */
final class ProtocolVersionTest extends TestCase
{
    #[Test]
    #[DataProvider('supported_versions')]
    public function negotiates_each_supported_version(string $version): void
    {
        $this->assertSame($version, ProtocolVersion::negotiate($version)->value);
    }

    public static function supported_versions(): iterable
    {
        yield '2024-11-05' => ['2024-11-05'];
        yield '2025-03-26' => ['2025-03-26'];
        yield '2025-06-18' => ['2025-06-18'];
        yield '2025-11-25' => ['2025-11-25'];
    }

    #[Test]
    public function unsupported_versions_negotiate_to_the_latest(): void
    {
        $this->assertSame(ProtocolVersion::LATEST, ProtocolVersion::negotiate(null));
        $this->assertSame(ProtocolVersion::LATEST, ProtocolVersion::negotiate('1999-01-01'));
        $this->assertSame(ProtocolVersion::LATEST, ProtocolVersion::negotiate('not-a-version'));
    }

    #[Test]
    public function supports_exactly_the_documented_versions(): void
    {
        $this->assertSame('2025-11-25', ProtocolVersion::LATEST->value);
        $this->assertEqualsCanonicalizing(
            ['2024-11-05', '2025-03-26', '2025-06-18', '2025-11-25'],
            array_column(ProtocolVersion::supported(), 'value'),
        );
    }
}
