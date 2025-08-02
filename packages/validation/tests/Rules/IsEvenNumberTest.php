<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsEvenNumber;

/**
 * @internal
 */
final class IsEvenNumberTest extends TestCase
{
    public function test_it_works(): void
    {
        $rule = new IsEvenNumber();

        $this->assertTrue($rule->isValid(4));
        $this->assertTrue($rule->isValid(2));
        $this->assertTrue($rule->isValid(0));

        $this->assertFalse($rule->isValid(1));
        $this->assertFalse($rule->isValid(3));
    }
}
