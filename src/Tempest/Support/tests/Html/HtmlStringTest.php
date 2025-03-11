<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Html;

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
    public function test_conversions(): void
    {
        $this->assertInstanceOf(Stringable::class, new HtmlString()); // @mago-expect php-unit/redundant-instance-of https://github.com/carthage-software/mago/issues/141
        $this->assertInstanceOf(MutableString::class, new HtmlString()->toMutableString());
        $this->assertInstanceOf(ImmutableString::class, new HtmlString()->toImmutableString());
        $this->assertSame('', new HtmlString()->toString());
    }

    public function test_create_from_tag(): void
    {
        $this->assertSame(
            expected: '<div></div>',
            actual: (string) HtmlString::createTag('div'),
        );
    }
}
