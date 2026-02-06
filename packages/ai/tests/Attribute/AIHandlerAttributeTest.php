<?php

declare(strict_types=1);

namespace Tempest\AI\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\AI\Attribute\AIHandler;

final class AIHandlerAttributeTest extends TestCase
{
    public function test_can_create_with_defaults(): void
    {
        $attribute = new AIHandler();

        $this->assertNull($attribute->model);
        $this->assertNull($attribute->temperature);
        $this->assertNull($attribute->maxTokens);
    }

    public function test_can_create_with_options(): void
    {
        $attribute = new AIHandler(
            model: 'gpt-4',
            temperature: 0.7,
            maxTokens: 2000,
        );

        $this->assertSame('gpt-4', $attribute->model);
        $this->assertSame(0.7, $attribute->temperature);
        $this->assertSame(2000, $attribute->maxTokens);
    }
}
