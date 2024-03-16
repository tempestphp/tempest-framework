<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Support;

use const DIRECTORY_SEPARATOR;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Path;

/**
 * @internal
 * @small
 */
class PathTest extends TestCase
{
    public function test_canonicalizing_a_path(): void
    {
        $path = __DIR__ . '/Fixtures/../PathTest.php';
        $canonicalizedPath = Path::canonicalize($path);

        $this->assertSame(realpath($path), $canonicalizedPath);
        $this->assertFalse(str_contains($canonicalizedPath, '..'));
    }

    public function test_normalizing_a_path(): void
    {
        $path = '\\This\\Directory\\Does\\Not\\Exist\\';

        $this->assertSame(
            DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, ['This', 'Directory', 'Does', 'Not', 'Exist']) . DIRECTORY_SEPARATOR,
            Path::normalize($path)
        );
    }

    public function test_joining_a_path(): void
    {
        $this->assertSame(
            join(DIRECTORY_SEPARATOR, ['This', 'Directory', 'Does', 'Not', 'Exist']),
            Path::join('This', 'Directory', 'Does', 'Not', 'Exist')
        );

        $this->assertSame(
            join(DIRECTORY_SEPARATOR, ['This', 'Directory']),
            Path::join('This\\Directory')
        );

        $this->assertSame(
            join(DIRECTORY_SEPARATOR, ['This', 'Directory']),
            Path::join('This/Directory')
        );
    }

    public function test_join_a_path_with(): void
    {
        $this->assertSame(
            join('\\', ['This', 'Directory', 'Does', 'Not', 'Exist']),
            Path::joinWith('\\', 'This', 'Directory', 'Does', 'Not', 'Exist')
        );

        $this->assertSame(
            join('\\', ['This', 'Directory']),
            Path::joinWith('\\', 'This\\Directory')
        );

        $this->assertSame(
            join('\\', ['This', 'Directory']),
            Path::joinWith('\\', 'This/Directory')
        );
    }
}
