<?php

declare(strict_types=1);

namespace Tempest\AI\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\AI\Config\OpenAIConfig;

final class OpenAIConfigTest extends TestCase
{
    public function test_default_config(): void
    {
        $config = new OpenAIConfig(apiKey: 'test-key');

        $this->assertSame('test-key', $config->apiKey);
        $this->assertSame('https://api.openai.com/v1', $config->baseUrl);
        $this->assertSame('gpt-4o', $config->defaultModel);
        $this->assertNull($config->organization);
    }

    public function test_custom_base_url(): void
    {
        $config = new OpenAIConfig(
            apiKey: 'test-key',
            baseUrl: 'https://api.azure.com/openai',
        );

        $this->assertSame('https://api.azure.com/openai', $config->baseUrl);
    }

    public function test_custom_model(): void
    {
        $config = new OpenAIConfig(
            apiKey: 'test-key',
            defaultModel: 'gpt-3.5-turbo',
        );

        $this->assertSame('gpt-3.5-turbo', $config->defaultModel);
    }

    public function test_organization(): void
    {
        $config = new OpenAIConfig(
            apiKey: 'test-key',
            organization: 'org-123456',
        );

        $this->assertSame('org-123456', $config->organization);
    }
}
