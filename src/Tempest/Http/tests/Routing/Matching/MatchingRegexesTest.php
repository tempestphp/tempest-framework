<?php

declare(strict_types=1);

namespace Tempest\Http\Tests\Routing\Matching;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Http\Routing\Matching\MatchingRegexes;
use Tempest\Http\Routing\Matching\RouteMatch;

/**
 * @internal
 */
final class MatchingRegexesTest extends TestCase
{
    private MatchingRegexes $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new MatchingRegexes([
            '#^(a)(*MARK:a)$#',
            '#^(b)(*MARK:b)$#',
            '#^(c)(*MARK:c)$#',
        ]);
    }

    public function test_empty(): void
    {
        $subject = new MatchingRegexes([]);

        $this->assertEquals(RouteMatch::notFound(), $subject->match(''));
    }

    #[TestWith(['a'])]
    #[TestWith(['b'])]
    #[TestWith(['c'])]
    public function test_match(string $expectedMatch): void
    {
        $match = $this->subject->match($expectedMatch);

        $this->assertTrue($match->isFound);
        $this->assertEquals($expectedMatch, $match->mark);
        $this->assertEquals($expectedMatch, $match->matches[1]);
    }

    #[TestWith([''])]
    #[TestWith(['d'])]
    public function test_non_match(string $expectedNonMatch): void
    {
        $match = $this->subject->match($expectedNonMatch);

        $this->assertEquals(RouteMatch::notFound(), $match);
    }
}
