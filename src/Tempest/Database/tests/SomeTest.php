<?php

namespace Tempest\Database\Tests;

use PHPUnit\Framework\TestCase;
use function Tempest\testFunction;

class SomeTest extends TestCase
{
    public function testSome()
    {
        $this->assertSame('a-test', testFunction());
    }
}