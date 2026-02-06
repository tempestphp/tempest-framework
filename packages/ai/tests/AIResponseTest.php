<?php

declare(strict_types=1);

namespace Tempest\AI\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\AI\AIResponse;

final class AIResponseTest extends TestCase
{
    public function test_can_create_response(): void
    {
        $response = new AIResponse(
            content: 'Hello, world!',
            model: 'gpt-4o',
            promptTokens: 10,
            completionTokens: 5,
            totalTokens: 15,
            finishReason: 'stop',
            raw: ['id' => 'test-123'],
        );

        $this->assertSame('Hello, world!', $response->content);
        $this->assertSame('gpt-4o', $response->model);
        $this->assertSame(10, $response->promptTokens);
        $this->assertSame(5, $response->completionTokens);
        $this->assertSame(15, $response->totalTokens);
        $this->assertSame('stop', $response->finishReason);
        $this->assertSame(['id' => 'test-123'], $response->raw);
    }

    public function test_can_create_minimal_response(): void
    {
        $response = new AIResponse(content: 'Simple response');

        $this->assertSame('Simple response', $response->content);
        $this->assertNull($response->model);
        $this->assertNull($response->promptTokens);
        $this->assertNull($response->completionTokens);
        $this->assertNull($response->totalTokens);
        $this->assertNull($response->finishReason);
        $this->assertSame([], $response->raw);
    }

    public function test_can_convert_to_string(): void
    {
        $response = new AIResponse(content: 'This is the content');

        $this->assertSame('This is the content', (string) $response);
    }

    public function test_empty_content(): void
    {
        $response = new AIResponse(content: '');

        $this->assertSame('', $response->content);
        $this->assertSame('', (string) $response);
    }
}
