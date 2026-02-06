<?php

declare(strict_types=1);

namespace Tempest\AI\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\AI\Config\AnthropicConfig;

final class AnthropicConfigTest extends TestCase
{
    public function test_default_config(): void
    {
        $config = new AnthropicConfig(apiKey: 'test-key');

        $this->assertSame('test-key', $config->apiKey);
        $this->assertSame('https://api.anthropic.com/v1', $config->baseUrl);
        $this->assertSame('claude-sonnet-4-20250514', $config->defaultModel);
        $this->assertSame('2023-06-01', $config->apiVersion);
    }

    public function test_custom_api_version(): void
    {
        $config = new AnthropicConfig(
            apiKey: 'test-key',
            apiVersion: '2024-01-01',
        );

        $this->assertSame('2024-01-01', $config->apiVersion);
    }

    public function test_custom_model(): void
    {
        $config = new AnthropicConfig(
            apiKey: 'test-key',
            defaultModel: 'claude-3-opus',
        );

        $this->assertSame('claude-3-opus', $config->defaultModel);
    }

    public function test_custom_base_url(): void
    {
        $config = new AnthropicConfig(
            apiKey: 'test-key',
            baseUrl: 'https://custom.anthropic.proxy.com/v1',
        );

        $this->assertSame('https://custom.anthropic.proxy.com/v1', $config->baseUrl);
    }
}
