<?php

declare(strict_types=1);

namespace Tempest\AI\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\AI\AIProvider;

final class AIProviderTest extends TestCase
{
    public function test_openai_provider(): void
    {
        $this->assertSame('openai', AIProvider::OPENAI->value);
    }

    public function test_anthropic_provider(): void
    {
        $this->assertSame('anthropic', AIProvider::ANTHROPIC->value);
    }

    public function test_can_create_from_string(): void
    {
        $this->assertSame(AIProvider::OPENAI, AIProvider::from('openai'));
        $this->assertSame(AIProvider::ANTHROPIC, AIProvider::from('anthropic'));
    }
}
