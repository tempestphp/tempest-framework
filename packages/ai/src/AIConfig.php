<?php

declare(strict_types=1);

namespace Tempest\AI;

use Tempest\AI\Config\AnthropicConfig;
use Tempest\AI\Config\OpenAIConfig;

final class AIConfig
{
    public function __construct(
        public AIProvider $defaultProvider = AIProvider::OPENAI,
        public ?OpenAIConfig $openai = null,
        public ?AnthropicConfig $anthropic = null,
        public ?string $defaultModel = null,
        public float $defaultTemperature = 0.7,
        public int $defaultMaxTokens = 1024,
    ) {
        $this->openai ??= new OpenAIConfig();
        $this->anthropic ??= new AnthropicConfig();
    }
}
