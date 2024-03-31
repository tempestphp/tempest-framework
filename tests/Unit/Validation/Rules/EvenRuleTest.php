<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation\Rules;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Even;
use Tempest\Validation\Rules\AfterDate;

/**
 * @internal
 * @small
 */
class EvenRuleTest extends TestCase
{
    public function test_it_works(): void
    {
        $rule = new Even();

        $this->assertTrue($rule->isValid(4));
        $this->assertTrue($rule->isValid(2));
        $this->assertTrue($rule->isValid(0));

        $this->assertFalse($rule->isValid(1));
        $this->assertFalse($rule->isValid(3));

        $this->assertSame('Value should be an even number', $rule->message());
    }

}
