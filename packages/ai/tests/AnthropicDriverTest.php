<?php

declare(strict_types=1);

namespace Tempest\AI\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\AI\AIConfig;
use Tempest\AI\AIMessage;
use Tempest\AI\AIProvider;
use Tempest\AI\Config\AnthropicConfig;
use Tempest\AI\Driver\AnthropicDriver;
use Tempest\AI\Exception\AIException;
use Tempest\Http\GenericResponse;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\HttpClient\HttpClient;

final class AnthropicDriverTest extends TestCase
{
    public function test_get_provider(): void
    {
        $httpClient = $this->createStub(HttpClient::class);
        $config = $this->createConfigWithApiKey();

        $driver = new AnthropicDriver($httpClient, $config);

        $this->assertSame(AIProvider::ANTHROPIC, $driver->getProvider());
    }

    public function test_throws_exception_when_api_key_not_configured(): void
    {
        $httpClient = $this->createStub(HttpClient::class);
        $config = new AIConfig(
            anthropic: new AnthropicConfig(apiKey: null),
        );

        $driver = new AnthropicDriver($httpClient, $config);

        $this->expectException(AIException::class);
        $this->expectExceptionMessage('Anthropic API key is not configured');

        $driver->chat([AIMessage::user('Hello')]);
    }

    public function test_successful_chat_request(): void
    {
        $responseData = [
            'id' => 'msg_123',
            'model' => 'claude-sonnet-4-20250514',
            'content' => [
                ['type' => 'text', 'text' => 'Hello! How can I help you?'],
            ],
            'stop_reason' => 'end_turn',
            'usage' => [
                'input_tokens' => 10,
                'output_tokens' => 8,
            ],
        ];

        $httpClient = $this->createMockHttpClient(json_encode($responseData));
        $config = $this->createConfigWithApiKey();
        $driver = new AnthropicDriver($httpClient, $config);

        $response = $driver->chat([AIMessage::user('Hello')]);

        $this->assertSame('Hello! How can I help you?', $response->content);
        $this->assertSame('claude-sonnet-4-20250514', $response->model);
        $this->assertSame(10, $response->promptTokens);
        $this->assertSame(8, $response->completionTokens);
        $this->assertSame(18, $response->totalTokens);
        $this->assertSame('end_turn', $response->finishReason);
    }

    public function test_throws_exception_on_api_error(): void
    {
        $responseData = [
            'error' => [
                'message' => 'Invalid API key',
            ],
        ];

        $httpClient = $this->createMockHttpClient(json_encode($responseData));
        $config = $this->createConfigWithApiKey();
        $driver = new AnthropicDriver($httpClient, $config);

        $this->expectException(AIException::class);
        $this->expectExceptionMessage('Anthropic API error: Invalid API key');

        $driver->chat([AIMessage::user('Hello')]);
    }

    public function test_extracts_system_message_correctly(): void
    {
        $responseData = [
            'content' => [['type' => 'text', 'text' => 'Response']],
        ];

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function (string $body) {
                    $data = json_decode($body, true);
                    // System should be extracted to top-level
                    return isset($data['system']) &&
                           $data['system'] === 'You are helpful.' &&
                           // Messages should not contain system
                           count($data['messages']) === 1 &&
                           $data['messages'][0]['role'] === 'user';
                }),
            )
            ->willReturn($this->createMockResponse(json_encode($responseData)));

        $config = $this->createConfigWithApiKey();
        $driver = new AnthropicDriver($httpClient, $config);

        $driver->chat([
            AIMessage::system('You are helpful.'),
            AIMessage::user('Hello'),
        ]);
    }

    public function test_uses_custom_model(): void
    {
        $responseData = [
            'content' => [['type' => 'text', 'text' => 'Response']],
            'model' => 'claude-3-opus',
        ];

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function (string $body) {
                    $data = json_decode($body, true);
                    return $data['model'] === 'claude-3-opus';
                }),
            )
            ->willReturn($this->createMockResponse(json_encode($responseData)));

        $config = $this->createConfigWithApiKey();
        $driver = new AnthropicDriver($httpClient, $config);

        $driver->chat([AIMessage::user('Hello')], model: 'claude-3-opus');
    }

    public function test_uses_custom_temperature(): void
    {
        $responseData = [
            'content' => [['type' => 'text', 'text' => 'Response']],
        ];

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function (string $body) {
                    $data = json_decode($body, true);
                    return $data['temperature'] === 0.3;
                }),
            )
            ->willReturn($this->createMockResponse(json_encode($responseData)));

        $config = $this->createConfigWithApiKey();
        $driver = new AnthropicDriver($httpClient, $config);

        $driver->chat([AIMessage::user('Hello')], temperature: 0.3);
    }

    public function test_uses_custom_max_tokens(): void
    {
        $responseData = [
            'content' => [['type' => 'text', 'text' => 'Response']],
        ];

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function (string $body) {
                    $data = json_decode($body, true);
                    return $data['max_tokens'] === 500;
                }),
            )
            ->willReturn($this->createMockResponse(json_encode($responseData)));

        $config = $this->createConfigWithApiKey();
        $driver = new AnthropicDriver($httpClient, $config);

        $driver->chat([AIMessage::user('Hello')], maxTokens: 500);
    }

    public function test_handles_multiple_content_blocks(): void
    {
        $responseData = [
            'content' => [
                ['type' => 'text', 'text' => 'First part. '],
                ['type' => 'text', 'text' => 'Second part.'],
            ],
        ];

        $httpClient = $this->createMockHttpClient(json_encode($responseData));
        $config = $this->createConfigWithApiKey();
        $driver = new AnthropicDriver($httpClient, $config);

        $response = $driver->chat([AIMessage::user('Hello')]);

        $this->assertSame('First part. Second part.', $response->content);
    }

    public function test_handles_empty_response(): void
    {
        $responseData = [
            'content' => [],
        ];

        $httpClient = $this->createMockHttpClient(json_encode($responseData));
        $config = $this->createConfigWithApiKey();
        $driver = new AnthropicDriver($httpClient, $config);

        $response = $driver->chat([AIMessage::user('Hello')]);

        $this->assertSame('', $response->content);
    }

    public function test_sends_correct_headers(): void
    {
        $responseData = [
            'content' => [['type' => 'text', 'text' => 'Response']],
        ];

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->with(
                $this->anything(),
                $this->callback(function (array $headers) {
                    return isset($headers['x-api-key']) &&
                           $headers['x-api-key'] === 'test-api-key' &&
                           isset($headers['anthropic-version']) &&
                           $headers['Content-Type'] === 'application/json';
                }),
                $this->anything(),
            )
            ->willReturn($this->createMockResponse(json_encode($responseData)));

        $config = $this->createConfigWithApiKey();
        $driver = new AnthropicDriver($httpClient, $config);

        $driver->chat([AIMessage::user('Hello')]);
    }

    private function createConfigWithApiKey(): AIConfig
    {
        return new AIConfig(
            anthropic: new AnthropicConfig(apiKey: 'test-api-key'),
        );
    }

    private function createMockHttpClient(string $responseBody): HttpClient
    {
        $httpClient = $this->createStub(HttpClient::class);
        $httpClient->method('post')->willReturn($this->createMockResponse($responseBody));

        return $httpClient;
    }

    private function createMockResponse(string $body): Response
    {
        return new GenericResponse(
            status: Status::OK,
            body: $body,
        );
    }
}
