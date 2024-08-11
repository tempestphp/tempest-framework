<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\RegEx;

/**
 * @internal
 * @small
 */
class RegexTest extends TestCase
{
    public function test_regex_rule(): void
    {
        $rule = new RegEx('/^[aA][bB]$/');

        $this->assertSame(
            'The value must match the regular expression pattern: /^[aA][bB]$/',
            $rule->message()
        );

        $this->assertFalse($rule->isValid('cd'));
        $this->assertFalse($rule->isValid('za'));

        $this->assertTrue($rule->isValid('ab'));
        $this->assertTrue($rule->isValid('AB'));
        $this->assertTrue($rule->isValid('Ab'));
    }
}
