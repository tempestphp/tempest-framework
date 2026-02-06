<?php

declare(strict_types=1);

namespace Tempest\AI\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\AI\AIProvider;
use Tempest\AI\Attribute\WithAI;

final class WithAIAttributeTest extends TestCase
{
    public function test_can_create_with_defaults(): void
    {
        $attribute = new WithAI();

        $this->assertNull($attribute->provider);
        $this->assertNull($attribute->model);
        $this->assertNull($attribute->temperature);
        $this->assertNull($attribute->maxTokens);
        $this->assertNull($attribute->systemPrompt);
    }

    public function test_can_create_with_provider(): void
    {
        $attribute = new WithAI(provider: AIProvider::ANTHROPIC);

        $this->assertSame(AIProvider::ANTHROPIC, $attribute->provider);
    }

    public function test_can_create_with_all_options(): void
    {
        $attribute = new WithAI(
            provider: AIProvider::OPENAI,
            model: 'gpt-4-turbo',
            temperature: 0.5,
            maxTokens: 1000,
            systemPrompt: 'Be helpful.',
        );

        $this->assertSame(AIProvider::OPENAI, $attribute->provider);
        $this->assertSame('gpt-4-turbo', $attribute->model);
        $this->assertSame(0.5, $attribute->temperature);
        $this->assertSame(1000, $attribute->maxTokens);
        $this->assertSame('Be helpful.', $attribute->systemPrompt);
    }
}
