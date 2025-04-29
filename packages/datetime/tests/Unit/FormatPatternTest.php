<?php

declare(strict_types=1);

namespace Tempest\DateTime\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tempest\DateTime\FormatPattern;

final class FormatPatternTest extends TestCase
{
    use DateTimeTestTrait;

    public function test_default(): void
    {
        $this->assertSame(FormatPattern::ISO8601, FormatPattern::default());
    }
}
