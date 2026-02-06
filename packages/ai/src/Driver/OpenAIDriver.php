<?php

declare(strict_types=1);

namespace Tempest\AI\Driver;

use Tempest\AI\AIConfig;
use Tempest\AI\AIDriver;
use Tempest\AI\AIMessage;
use Tempest\AI\AIProvider;
use Tempest\AI\AIResponse;
use Tempest\AI\Exception\AIException;
use Tempest\HttpClient\HttpClient;

final readonly class OpenAIDriver implements AIDriver
{
    public function __construct(
        private HttpClient $httpClient,
        private AIConfig $config,
    ) {}

    public function chat(
        array $messages,
        ?string $model = null,
        ?float $temperature = null,
        ?int $maxTokens = null,
    ): AIResponse {
        $openaiConfig = $this->config->openai;

        if ($openaiConfig?->apiKey === null) {
            throw new AIException('OpenAI API key is not configured. Set OPENAI_API_KEY environment variable or configure it in AIConfig.');
        }

        $model ??= $this->config->defaultModel ?? $openaiConfig->defaultModel;
        $temperature ??= $this->config->defaultTemperature;
        $maxTokens ??= $this->config->defaultMaxTokens;

        $headers = [
            'Authorization' => 'Bearer ' . $openaiConfig->apiKey,
            'Content-Type' => 'application/json',
        ];

        if ($openaiConfig->organization !== null) {
            $headers['OpenAI-Organization'] = $openaiConfig->organization;
        }

        $body = json_encode([
            'model' => $model,
            'messages' => array_map(
                fn(AIMessage $message) => $message->toArray(),
                $messages,
            ),
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ], JSON_THROW_ON_ERROR);

        $response = $this->httpClient->post(
            uri: $openaiConfig->baseUrl . '/chat/completions',
            headers: $headers,
            body: $body,
        );

        $responseBody = is_string($response->body) ? $response->body : json_encode($response->body);
        $data = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);

        if (isset($data['error'])) {
            throw new AIException('OpenAI API error: ' . ($data['error']['message'] ?? 'Unknown error'));
        }

        $choice = $data['choices'][0] ?? null;
        $usage = $data['usage'] ?? [];

        return new AIResponse(
            content: $choice['message']['content'] ?? '',
            model: $data['model'] ?? $model,
            promptTokens: $usage['prompt_tokens'] ?? null,
            completionTokens: $usage['completion_tokens'] ?? null,
            totalTokens: $usage['total_tokens'] ?? null,
            finishReason: $choice['finish_reason'] ?? null,
            raw: $data,
        );
    }

    public function getProvider(): AIProvider
    {
        return AIProvider::OPENAI;
    }
}
