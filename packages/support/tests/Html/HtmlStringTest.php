<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Html;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stringable;
use Tempest\Support\Html\HtmlString;
use Tempest\Support\Str\ImmutableString;
use Tempest\Support\Str\MutableString;

/**
 * @internal
 */
final class HtmlStringTest extends TestCase
{
    #[Test]
    public function conversions(): void
    {
        $this->assertInstanceOf(Stringable::class, new HtmlString());
        $this->assertInstanceOf(MutableString::class, new HtmlString()->toMutableString());
        $this->assertInstanceOf(ImmutableString::class, new HtmlString()->toImmutableString());
        $this->assertSame('', new HtmlString()->toString());
    }

    #[Test]
    public function create_from_tag(): void
    {
        $this->assertSame(
            expected: '<div></div>',
            actual: (string) HtmlString::createTag('div'),
        );
    }
}
