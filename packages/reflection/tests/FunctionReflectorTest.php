<?php

namespace Tempest\Reflection\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Reflection\FunctionReflector;

final class FunctionReflectorTest extends TestCase
{
    public function test_get_parameter(): void
    {
        $reflector = new FunctionReflector(fn (string $_test) => null);

        $this->assertSame('_test', $reflector->getParameter(key: '_test')->getName());
        $this->assertSame('_test', $reflector->getParameter(key: 0)->getName());
        $this->assertNull($reflector->getParameter(key: 'does-not-exist'));
    }
}
