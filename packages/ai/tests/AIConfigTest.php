<?php

declare(strict_types=1);

namespace Tempest\AI\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\AI\AIConfig;
use Tempest\AI\AIProvider;
use Tempest\AI\Config\AnthropicConfig;
use Tempest\AI\Config\OpenAIConfig;

final class AIConfigTest extends TestCase
{
    public function test_default_config(): void
    {
        $config = new AIConfig();

        $this->assertSame(AIProvider::OPENAI, $config->defaultProvider);
        $this->assertSame(0.7, $config->defaultTemperature);
        $this->assertSame(1024, $config->defaultMaxTokens);
        $this->assertNull($config->defaultModel);
        $this->assertInstanceOf(OpenAIConfig::class, $config->openai);
        $this->assertInstanceOf(AnthropicConfig::class, $config->anthropic);
    }

    public function test_custom_config(): void
    {
        $config = new AIConfig(
            defaultProvider: AIProvider::ANTHROPIC,
            defaultModel: 'custom-model',
            defaultTemperature: 0.5,
            defaultMaxTokens: 2048,
        );

        $this->assertSame(AIProvider::ANTHROPIC, $config->defaultProvider);
        $this->assertSame('custom-model', $config->defaultModel);
        $this->assertSame(0.5, $config->defaultTemperature);
        $this->assertSame(2048, $config->defaultMaxTokens);
    }

    public function test_custom_openai_config(): void
    {
        $openaiConfig = new OpenAIConfig(
            apiKey: 'test-api-key',
            baseUrl: 'https://custom.api.com/v1',
            defaultModel: 'gpt-4-turbo',
            organization: 'test-org',
        );

        $config = new AIConfig(openai: $openaiConfig);

        $this->assertSame('test-api-key', $config->openai->apiKey);
        $this->assertSame('https://custom.api.com/v1', $config->openai->baseUrl);
        $this->assertSame('gpt-4-turbo', $config->openai->defaultModel);
        $this->assertSame('test-org', $config->openai->organization);
    }

    public function test_custom_anthropic_config(): void
    {
        $anthropicConfig = new AnthropicConfig(
            apiKey: 'test-anthropic-key',
            baseUrl: 'https://custom.anthropic.com/v1',
            defaultModel: 'claude-3-opus',
            apiVersion: '2024-01-01',
        );

        $config = new AIConfig(anthropic: $anthropicConfig);

        $this->assertSame('test-anthropic-key', $config->anthropic->apiKey);
        $this->assertSame('https://custom.anthropic.com/v1', $config->anthropic->baseUrl);
        $this->assertSame('claude-3-opus', $config->anthropic->defaultModel);
        $this->assertSame('2024-01-01', $config->anthropic->apiVersion);
    }
}
