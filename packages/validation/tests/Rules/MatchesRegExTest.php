<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\MatchesRegEx;

/**
 * @internal
 */
final class MatchesRegExTest extends TestCase
{
    public function test_regex_rule(): void
    {
        $rule = new MatchesRegEx('/^[aA][bB]$/');

        $this->assertFalse($rule->isValid('cd'));
        $this->assertFalse($rule->isValid('za'));

        $this->assertTrue($rule->isValid('ab'));
        $this->assertTrue($rule->isValid('AB'));
        $this->assertTrue($rule->isValid('Ab'));
    }

    public function test_non_imvalid_types(): void
    {
        $rule = new MatchesRegEx('/^[0-9]+$/');

        // Invalid types should return false, not a TypeError.
        $this->assertFalse($rule->isValid(false));
        $this->assertFalse($rule->isValid([]));
        $this->assertFalse($rule->isValid(new \stdClass()));
        $this->assertFalse($rule->isValid(null));
    }
}
