<?php

declare(strict_types=1);

namespace Tempest\Database\Tests;

use PHPUnit\Framework\TestCase;
use function Tempest\testFunction;

/**
 * @internal
 */
final class SomeTest extends TestCase
{
    public function test_some()
    {
        $this->assertSame('a-test', testFunction());
    }
}
