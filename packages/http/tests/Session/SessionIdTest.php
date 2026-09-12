<?php

declare(strict_types=1);

namespace Tempest\Http\Tests\Session;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Http\Session\InvalidSessionId;
use Tempest\Http\Session\SessionId;

/**
 * @internal
 */
final class SessionIdTest extends TestCase
{
    #[Test]
    #[TestWith(['0197c5f0-8b3a-7c2e-9d1f-2a3b4c5d6e7f'])] // UUID (cookie/header resolver)
    #[TestWith(['test-session'])]
    #[TestWith(['abcDEF_123-456'])]
    public function accepts_valid_identifiers(string $id): void
    {
        $this->assertSame($id, (string) new SessionId($id));
    }

    #[Test]
    #[TestWith(['../../../../etc/passwd'])]
    #[TestWith(['../../tmp/marker'])]
    #[TestWith(['foo/bar'])]
    #[TestWith(['with space'])]
    #[TestWith(['with.dot'])]
    #[TestWith(["null\0byte"])]
    #[TestWith(["trailing-newline\n"])]
    #[TestWith([''])]
    public function rejects_traversal_and_unsafe_identifiers(string $id): void
    {
        $this->expectException(InvalidSessionId::class);

        new SessionId($id);
    }

    #[Test]
    public function rejects_overly_long_identifiers(): void
    {
        $this->expectException(InvalidSessionId::class);

        new SessionId(str_repeat('a', 129));
    }
}
