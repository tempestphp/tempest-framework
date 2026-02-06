<?php

declare(strict_types=1);

namespace Tempest\AI\Config;

final class OpenAIConfig
{
    public function __construct(
        public ?string $apiKey = null,
        public string $baseUrl = 'https://api.openai.com/v1',
        public string $defaultModel = 'gpt-4o',
        public ?string $organization = null,
    ) {
        $this->apiKey ??= $_ENV['OPENAI_API_KEY'] ?? null;
        $this->organization ??= $_ENV['OPENAI_ORGANIZATION'] ?? null;
    }
}
