<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsTimezone;

/**
 * @internal
 */
final class IsTimezoneTest extends TestCase
{
    public function test_timezone(): void
    {
        $rule = new IsTimezone();

        $this->assertFalse($rule->isValid('invalid_timezone'));
        $this->assertFalse($rule->isValid('Asia/Sydney'));
        $this->assertTrue($rule->isValid('America/New_York'));
        $this->assertTrue($rule->isValid('Europe/London'));
        $this->assertTrue($rule->isValid('Europe/Paris'));
        $this->assertTrue($rule->isValid('UTC'));
    }

    public function test_timezone_with_country_code(): void
    {
        $rule = new IsTimezone(DateTimeZone::PER_COUNTRY, 'AU');

        $this->assertFalse($rule->isValid('America/New_York'));
        $this->assertTrue($rule->isValid('Australia/Sydney'));
        $this->assertTrue($rule->isValid('Australia/Melbourne'));

        $rule = new IsTimezone(DateTimeZone::PER_COUNTRY, 'US');

        $this->assertFalse($rule->isValid('Europe/Paris'));
        $this->assertTrue($rule->isValid('America/New_York'));
        $this->assertTrue($rule->isValid('America/Los_Angeles'));
        $this->assertTrue($rule->isValid('America/Chicago'));
    }

    public function test_timezone_with_group(): void
    {
        $rule = new IsTimezone(DateTimeZone::ASIA);

        $this->assertFalse($rule->isValid('Africa/Nairobi'));
        $this->assertTrue($rule->isValid('Asia/Tokyo'));
        $this->assertTrue($rule->isValid('Asia/Hong_Kong'));
        $this->assertTrue($rule->isValid('Asia/Singapore'));

        $rule = new IsTimezone(DateTimeZone::INDIAN);

        $this->assertFalse($rule->isValid('Europe/Paris'));
        $this->assertTrue($rule->isValid('Indian/Reunion'));
        $this->assertTrue($rule->isValid('Indian/Comoro'));
    }
}
