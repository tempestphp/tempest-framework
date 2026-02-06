<?php

declare(strict_types=1);

namespace Tempest\AI\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\AI\MessageRole;

final class MessageRoleTest extends TestCase
{
    public function test_system_role(): void
    {
        $this->assertSame('system', MessageRole::SYSTEM->value);
    }

    public function test_user_role(): void
    {
        $this->assertSame('user', MessageRole::USER->value);
    }

    public function test_assistant_role(): void
    {
        $this->assertSame('assistant', MessageRole::ASSISTANT->value);
    }

    public function test_can_create_from_string(): void
    {
        $this->assertSame(MessageRole::SYSTEM, MessageRole::from('system'));
        $this->assertSame(MessageRole::USER, MessageRole::from('user'));
        $this->assertSame(MessageRole::ASSISTANT, MessageRole::from('assistant'));
    }
}
