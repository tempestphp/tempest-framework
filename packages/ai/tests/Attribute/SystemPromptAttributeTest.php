<?php

declare(strict_types=1);

namespace Tempest\AI\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\AI\Attribute\SystemPrompt;

final class SystemPromptAttributeTest extends TestCase
{
    public function test_can_create_with_prompt(): void
    {
        $attribute = new SystemPrompt('You are a helpful assistant.');

        $this->assertSame('You are a helpful assistant.', $attribute->prompt);
    }

    public function test_prompt_is_required(): void
    {
        $reflection = new \ReflectionClass(SystemPrompt::class);
        $constructor = $reflection->getConstructor();
        $params = $constructor->getParameters();

        $this->assertCount(1, $params);
        $this->assertSame('prompt', $params[0]->getName());
        $this->assertFalse($params[0]->isOptional());
    }
}
