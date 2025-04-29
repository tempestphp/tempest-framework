<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Unit\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\RegEx;

/**
 * @internal
 */
final class RegexTest extends TestCase
{
    public function test_regex_rule(): void
    {
        $rule = new RegEx('/^[aA][bB]$/');

        $this->assertSame(
            'The value must match the regular expression pattern: /^[aA][bB]$/',
            $rule->message(),
        );

        $this->assertFalse($rule->isValid('cd'));
        $this->assertFalse($rule->isValid('za'));

        $this->assertTrue($rule->isValid('ab'));
        $this->assertTrue($rule->isValid('AB'));
        $this->assertTrue($rule->isValid('Ab'));
    }

    public function test_non_imvalid_types(): void
    {
        $rule = new RegEx('/^[0-9]+$/');

        // Invalid types should return false, not a TypeError.
        $this->assertFalse($rule->isValid(false));
        $this->assertFalse($rule->isValid([]));
        $this->assertFalse($rule->isValid(new \stdClass()));
        $this->assertFalse($rule->isValid(null));
    }
}
