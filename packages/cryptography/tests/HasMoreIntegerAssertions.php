<?php

namespace Tempest\Cryptography\Tests;

use PHPUnit\Framework\ExpectationFailedException;

trait HasMoreIntegerAssertions
{
    private function assertEqualsToMoreOrLess(int|float $expected, int|float $actual, int|float $margin): void
    {
        try {
            $this->assertGreaterThanOrEqual($expected - $margin, $actual);
            $this->assertLessThanOrEqual($expected + $margin, $actual);
        } catch (ExpectationFailedException $e) {
            throw new ExpectationFailedException(
                message: sprintf('Expected value to be within %s of %s, but got %s', $margin, $expected, $actual),
                comparisonFailure: $e->getComparisonFailure(),
            );
        }
    }
}
