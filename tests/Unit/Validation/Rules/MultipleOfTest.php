<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation\Rules;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Even;
use Tempest\Validation\Rules\AfterDate;
use Tempest\Validation\Rules\MultipleOf;
use Tempest\Validation\Rules\DivisibleBy;

/**
 * @internal
 * @small
 */
class MultipleOfTest extends TestCase
{
    public function test_it_works(): void
    {
        $rule = new MultipleOf(5);

        $this->assertTrue($rule->isValid(10));
        $this->assertTrue($rule->isValid(5));
        $this->assertTrue($rule->isValid(0));

        $this->assertFalse($rule->isValid(3));
        $this->assertFalse($rule->isValid(4));
        $this->assertFalse($rule->isValid(6));

        $this->assertSame('Value should be a multiple of 5', $rule->message());
    }

}
