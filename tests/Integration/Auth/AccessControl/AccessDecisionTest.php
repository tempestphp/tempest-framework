<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth\AccessControl;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Auth\AccessControl\AccessDecision;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class AccessDecisionTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function can_create_denied_decision_with_message(): void
    {
        $message = 'access denied';
        $decision = AccessDecision::denied($message);

        $this->assertFalse($decision->granted);
        $this->assertEquals($message, $decision->message);
    }

    #[Test]
    #[TestWith([true, true])]
    #[TestWith([false, false])]
    #[TestWith([null, false])]
    public function from_method_handles_booleans(mixed $input, mixed $expected): void
    {
        $decision = AccessDecision::from($input);

        $this->assertSame($expected, $decision->granted);
        $this->assertNull($decision->message);
    }
}
