<?php

declare(strict_types=1);

namespace Tempest\AI\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\AI\Attribute\CacheAIResponse;

final class CacheAIResponseAttributeTest extends TestCase
{
    public function test_can_create_with_defaults(): void
    {
        $attribute = new CacheAIResponse();

        $this->assertSame(3600, $attribute->ttl);
        $this->assertNull($attribute->key);
    }

    public function test_can_create_with_custom_ttl(): void
    {
        $attribute = new CacheAIResponse(ttl: 7200);

        $this->assertSame(7200, $attribute->ttl);
    }

    public function test_can_create_with_custom_key(): void
    {
        $attribute = new CacheAIResponse(ttl: 1800, key: 'my_cache_prefix');

        $this->assertSame(1800, $attribute->ttl);
        $this->assertSame('my_cache_prefix', $attribute->key);
    }
}
