<?php

declare(strict_types=1);

namespace Tempest\AI\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\AI\AIMessage;
use Tempest\AI\MessageRole;

final class AIMessageTest extends TestCase
{
    public function test_can_create_system_message(): void
    {
        $message = AIMessage::system('You are a helpful assistant.');

        $this->assertSame(MessageRole::SYSTEM, $message->role);
        $this->assertSame('You are a helpful assistant.', $message->content);
    }

    public function test_can_create_user_message(): void
    {
        $message = AIMessage::user('Hello, how are you?');

        $this->assertSame(MessageRole::USER, $message->role);
        $this->assertSame('Hello, how are you?', $message->content);
    }

    public function test_can_create_assistant_message(): void
    {
        $message = AIMessage::assistant('I am doing well, thank you!');

        $this->assertSame(MessageRole::ASSISTANT, $message->role);
        $this->assertSame('I am doing well, thank you!', $message->content);
    }

    public function test_can_convert_to_array(): void
    {
        $message = AIMessage::user('Test message');

        $this->assertSame([
            'role' => 'user',
            'content' => 'Test message',
        ], $message->toArray());
    }
}
