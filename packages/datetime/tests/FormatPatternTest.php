<?php

declare(strict_types=1);

namespace Tempest\DateTime\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\DateTime\FormatPattern;

final class FormatPatternTest extends TestCase
{
    use DateTimeTestTrait;

    #[Test]
    public function default(): void
    {
        $this->assertSame(FormatPattern::ISO8601, FormatPattern::default());
    }
}
