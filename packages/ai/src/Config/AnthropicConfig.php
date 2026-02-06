<?php

declare(strict_types=1);

namespace Tempest\AI\Config;

final class AnthropicConfig
{
    public function __construct(
        public ?string $apiKey = null,
        public string $baseUrl = 'https://api.anthropic.com/v1',
        public string $defaultModel = 'claude-sonnet-4-20250514',
        public string $apiVersion = '2023-06-01',
    ) {
        $this->apiKey ??= $_ENV['ANTHROPIC_API_KEY'] ?? null;
    }
}
