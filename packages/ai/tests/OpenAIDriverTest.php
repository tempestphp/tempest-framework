<?php

declare(strict_types=1);

namespace Tempest\AI\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\AI\AIConfig;
use Tempest\AI\AIMessage;
use Tempest\AI\AIProvider;
use Tempest\AI\Config\OpenAIConfig;
use Tempest\AI\Driver\OpenAIDriver;
use Tempest\AI\Exception\AIException;
use Tempest\Http\GenericResponse;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\HttpClient\HttpClient;

final class OpenAIDriverTest extends TestCase
{
    public function test_get_provider(): void
    {
        $httpClient = $this->createStub(HttpClient::class);
        $config = $this->createConfigWithApiKey();

        $driver = new OpenAIDriver($httpClient, $config);

        $this->assertSame(AIProvider::OPENAI, $driver->getProvider());
    }

    public function test_throws_exception_when_api_key_not_configured(): void
    {
        $httpClient = $this->createStub(HttpClient::class);
        $config = new AIConfig(
            openai: new OpenAIConfig(apiKey: null),
        );

        $driver = new OpenAIDriver($httpClient, $config);

        $this->expectException(AIException::class);
        $this->expectExceptionMessage('OpenAI API key is not configured');

        $driver->chat([AIMessage::user('Hello')]);
    }

    public function test_successful_chat_request(): void
    {
        $responseData = [
            'id' => 'chatcmpl-123',
            'model' => 'gpt-4o',
            'choices' => [
                [
                    'message' => ['content' => 'Hello! How can I help you?'],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => [
                'prompt_tokens' => 10,
                'completion_tokens' => 8,
                'total_tokens' => 18,
            ],
        ];

        $httpClient = $this->createMockHttpClient(json_encode($responseData));
        $config = $this->createConfigWithApiKey();
        $driver = new OpenAIDriver($httpClient, $config);

        $response = $driver->chat([AIMessage::user('Hello')]);

        $this->assertSame('Hello! How can I help you?', $response->content);
        $this->assertSame('gpt-4o', $response->model);
        $this->assertSame(10, $response->promptTokens);
        $this->assertSame(8, $response->completionTokens);
        $this->assertSame(18, $response->totalTokens);
        $this->assertSame('stop', $response->finishReason);
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
        $driver = new OpenAIDriver($httpClient, $config);

        $this->expectException(AIException::class);
        $this->expectExceptionMessage('OpenAI API error: Invalid API key');

        $driver->chat([AIMessage::user('Hello')]);
    }

    public function test_uses_custom_model(): void
    {
        $responseData = [
            'choices' => [['message' => ['content' => 'Response']]],
            'model' => 'gpt-3.5-turbo',
        ];

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function (string $body) {
                    $data = json_decode($body, true);
                    return $data['model'] === 'gpt-3.5-turbo';
                }),
            )
            ->willReturn($this->createMockResponse(json_encode($responseData)));

        $config = $this->createConfigWithApiKey();
        $driver = new OpenAIDriver($httpClient, $config);

        $driver->chat([AIMessage::user('Hello')], model: 'gpt-3.5-turbo');
    }

    public function test_uses_custom_temperature(): void
    {
        $responseData = [
            'choices' => [['message' => ['content' => 'Response']]],
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
        $driver = new OpenAIDriver($httpClient, $config);

        $driver->chat([AIMessage::user('Hello')], temperature: 0.3);
    }

    public function test_uses_custom_max_tokens(): void
    {
        $responseData = [
            'choices' => [['message' => ['content' => 'Response']]],
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
        $driver = new OpenAIDriver($httpClient, $config);

        $driver->chat([AIMessage::user('Hello')], maxTokens: 500);
    }

    public function test_handles_empty_response(): void
    {
        $responseData = [
            'choices' => [['message' => ['content' => '']]],
        ];

        $httpClient = $this->createMockHttpClient(json_encode($responseData));
        $config = $this->createConfigWithApiKey();
        $driver = new OpenAIDriver($httpClient, $config);

        $response = $driver->chat([AIMessage::user('Hello')]);

        $this->assertSame('', $response->content);
    }

    private function createConfigWithApiKey(): AIConfig
    {
        return new AIConfig(
            openai: new OpenAIConfig(apiKey: 'test-api-key'),
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
