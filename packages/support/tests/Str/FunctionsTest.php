<?php

namespace Tempest\Support\Tests\Str;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Tempest\Support\Str;
use Tempest\Support\Str\ImmutableString;
use Tempest\Support\Str\MutableString;

final class FunctionsTest extends TestCase
{
    #[Test]
    public function parse(): void
    {
        $this->assertSame('foo', Str\parse('foo'));
        $this->assertSame('1', Str\parse('1'));
        $this->assertSame('1', Str\parse(1));
        $this->assertSame('', Str\parse(new stdClass()));
        $this->assertNull(Str\parse(new stdClass(), default: null));
        $this->assertSame('', Str\parse(new stdClass(), default: ''));
        $this->assertSame('foo', Str\parse(new stdClass(), default: 'foo'));
        $this->assertSame('foo', Str\parse(new MutableString('foo')));
        $this->assertSame('foo', Str\parse(new ImmutableString('foo')));
        $this->assertSame('', Str\parse(['a', 'b']));
    }
}
