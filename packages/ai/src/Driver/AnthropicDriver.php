<?php

declare(strict_types=1);

namespace Tempest\AI\Driver;

use Tempest\AI\AIConfig;
use Tempest\AI\AIDriver;
use Tempest\AI\AIMessage;
use Tempest\AI\AIProvider;
use Tempest\AI\AIResponse;
use Tempest\AI\Exception\AIException;
use Tempest\AI\MessageRole;
use Tempest\HttpClient\HttpClient;

final readonly class AnthropicDriver implements AIDriver
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
        $anthropicConfig = $this->config->anthropic;

        if ($anthropicConfig?->apiKey === null) {
            throw new AIException('Anthropic API key is not configured. Set ANTHROPIC_API_KEY environment variable or configure it in AIConfig.');
        }

        $model ??= $this->config->defaultModel ?? $anthropicConfig->defaultModel;
        $temperature ??= $this->config->defaultTemperature;
        $maxTokens ??= $this->config->defaultMaxTokens;

        // Extract system message if present (Anthropic handles it differently)
        $systemPrompt = null;
        $chatMessages = [];

        foreach ($messages as $message) {
            if ($message->role === MessageRole::SYSTEM) {
                $systemPrompt = $message->content;
            } else {
                $chatMessages[] = $message->toArray();
            }
        }

        $headers = [
            'x-api-key' => $anthropicConfig->apiKey,
            'Content-Type' => 'application/json',
            'anthropic-version' => $anthropicConfig->apiVersion,
        ];

        $payload = [
            'model' => $model,
            'messages' => $chatMessages,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
        ];

        if ($systemPrompt !== null) {
            $payload['system'] = $systemPrompt;
        }

        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $response = $this->httpClient->post(
            uri: $anthropicConfig->baseUrl . '/messages',
            headers: $headers,
            body: $body,
        );

        $responseBody = is_string($response->body) ? $response->body : json_encode($response->body);
        $data = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);

        if (isset($data['error'])) {
            throw new AIException('Anthropic API error: ' . ($data['error']['message'] ?? 'Unknown error'));
        }

        $content = '';
        foreach ($data['content'] ?? [] as $block) {
            if ($block['type'] === 'text') {
                $content .= $block['text'];
            }
        }

        $usage = $data['usage'] ?? [];

        return new AIResponse(
            content: $content,
            model: $data['model'] ?? $model,
            promptTokens: $usage['input_tokens'] ?? null,
            completionTokens: $usage['output_tokens'] ?? null,
            totalTokens: ($usage['input_tokens'] ?? 0) + ($usage['output_tokens'] ?? 0) ?: null,
            finishReason: $data['stop_reason'] ?? null,
            raw: $data,
        );
    }

    public function getProvider(): AIProvider
    {
        return AIProvider::ANTHROPIC;
    }
}
