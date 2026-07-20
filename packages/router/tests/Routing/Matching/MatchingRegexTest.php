<?php

declare(strict_types=1);

namespace Tempest\Router\Tests\Routing\Matching;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Router\Routing\Matching\MatchingRegex;

/**
 * @internal
 */
final class MatchingRegexTest extends TestCase
{
    private MatchingRegex $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new MatchingRegex([
            '#^(a)(*MARK:a)$#',
            '#^(b)(*MARK:b)$#',
            '#^(c)(*MARK:c)$#',
        ]);
    }

    #[Test]
    public function empty(): void
    {
        $subject = new MatchingRegex([]);

        $this->assertNull($subject->match(''));
    }

    #[TestWith(['a'])]
    #[TestWith(['b'])]
    #[TestWith(['c'])]
    #[Test]
    public function match(string $expectedMatch): void
    {
        $match = $this->subject->match($expectedMatch);

        $this->assertNotNull($match);
        $this->assertEquals($expectedMatch, $match->mark);
        $this->assertEquals($expectedMatch, $match->matches[1]);
    }

    #[TestWith([''])]
    #[TestWith(['d'])]
    #[Test]
    public function non_match(string $expectedNonMatch): void
    {
        $match = $this->subject->match($expectedNonMatch);

        $this->assertNull($match);
    }
}
