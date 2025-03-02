<?php

declare(strict_types=1);

namespace Tempest\Support\Tests;

use PHPUnit\Framework\TestCase;
use function Tempest\Support\tap;

/**
 * @internal
 */
final class FunctionsTest extends TestCase
{
    public function test_tap(): void
    {
        $class = new class () {
            public string $value = 'foo';
        };

        $log = '';

        $result = tap($class, static function (mixed $x) use (&$log): void {
            $log .= $x->value;
        });

        $this->assertSame($result, $class);
        $this->assertSame('foo', $log);
    }
}
