<?php

declare(strict_types=1);

namespace Tempest\AI\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\AI\AIConfig;
use Tempest\AI\AIDriver;
use Tempest\AI\AIMessage;
use Tempest\AI\AIProvider;
use Tempest\AI\AIResponse;
use Tempest\AI\Exception\AIException;
use Tempest\AI\GenericAIChat;

final class GenericAIChatTest extends TestCase
{
    public function test_can_prompt(): void
    {
        $config = new AIConfig();
        $chat = new GenericAIChat($config);

        $mockDriver = $this->createMockDriver(
            provider: AIProvider::OPENAI,
            response: new AIResponse(content: 'Hello from OpenAI!'),
        );

        $chat->addDriver($mockDriver);

        $response = $chat->prompt('Hello');

        $this->assertSame('Hello from OpenAI!', $response->content);
    }

    public function test_can_chat_with_messages(): void
    {
        $config = new AIConfig();
        $chat = new GenericAIChat($config);

        $mockDriver = $this->createMockDriver(
            provider: AIProvider::OPENAI,
            response: new AIResponse(content: 'Response to conversation'),
        );

        $chat->addDriver($mockDriver);

        $response = $chat->chat([
            AIMessage::system('You are helpful.'),
            AIMessage::user('Hello'),
        ]);

        $this->assertSame('Response to conversation', $response->content);
    }

    public function test_can_switch_provider(): void
    {
        $config = new AIConfig(defaultProvider: AIProvider::OPENAI);
        $chat = new GenericAIChat($config);

        $openaiDriver = $this->createMockDriver(
            provider: AIProvider::OPENAI,
            response: new AIResponse(content: 'OpenAI response'),
        );

        $anthropicDriver = $this->createMockDriver(
            provider: AIProvider::ANTHROPIC,
            response: new AIResponse(content: 'Anthropic response'),
        );

        $chat->addDriver($openaiDriver);
        $chat->addDriver($anthropicDriver);

        // Default is OpenAI
        $response = $chat->prompt('Hello');
        $this->assertSame('OpenAI response', $response->content);

        // Switch to Anthropic
        $response = $chat->using(AIProvider::ANTHROPIC)->prompt('Hello');
        $this->assertSame('Anthropic response', $response->content);
    }

    public function test_with_model_returns_new_instance(): void
    {
        $config = new AIConfig();
        $chat = new GenericAIChat($config);

        $newChat = $chat->withModel('gpt-4');

        $this->assertNotSame($chat, $newChat);
    }

    public function test_with_temperature_returns_new_instance(): void
    {
        $config = new AIConfig();
        $chat = new GenericAIChat($config);

        $newChat = $chat->withTemperature(0.5);

        $this->assertNotSame($chat, $newChat);
    }

    public function test_with_max_tokens_returns_new_instance(): void
    {
        $config = new AIConfig();
        $chat = new GenericAIChat($config);

        $newChat = $chat->withMaxTokens(2048);

        $this->assertNotSame($chat, $newChat);
    }

    public function test_with_system_prompt_returns_new_instance(): void
    {
        $config = new AIConfig();
        $chat = new GenericAIChat($config);

        $newChat = $chat->withSystemPrompt('Be helpful.');

        $this->assertNotSame($chat, $newChat);
    }

    public function test_using_returns_new_instance(): void
    {
        $config = new AIConfig();
        $chat = new GenericAIChat($config);

        $newChat = $chat->using(AIProvider::ANTHROPIC);

        $this->assertNotSame($chat, $newChat);
    }

    public function test_throws_exception_when_no_driver_configured(): void
    {
        $config = new AIConfig();
        $chat = new GenericAIChat($config);

        $this->expectException(AIException::class);
        $this->expectExceptionMessage('No driver configured for provider: openai');

        $chat->prompt('Hello');
    }

    public function test_fluent_api(): void
    {
        $config = new AIConfig();
        $chat = new GenericAIChat($config);

        $mockDriver = $this->createMockDriver(
            provider: AIProvider::ANTHROPIC,
            response: new AIResponse(content: 'Fluent response'),
        );

        $chat->addDriver($mockDriver);

        $response = $chat
            ->using(AIProvider::ANTHROPIC)
            ->withModel('claude-3-opus')
            ->withTemperature(0.3)
            ->withMaxTokens(500)
            ->withSystemPrompt('Be concise.')
            ->prompt('Explain PHP');

        $this->assertSame('Fluent response', $response->content);
    }

    private function createMockDriver(AIProvider $provider, AIResponse $response): AIDriver
    {
        $driver = $this->createStub(AIDriver::class);

        $driver->method('getProvider')->willReturn($provider);
        $driver->method('chat')->willReturn($response);

        return $driver;
    }
}
