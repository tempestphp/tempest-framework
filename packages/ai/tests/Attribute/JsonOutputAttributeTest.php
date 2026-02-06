<?php

declare(strict_types=1);

namespace Tempest\AI\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\AI\Attribute\JsonOutput;

final class JsonOutputAttributeTest extends TestCase
{
    public function test_can_create_with_defaults(): void
    {
        $attribute = new JsonOutput();

        $this->assertNull($attribute->schema);
    }

    public function test_can_create_with_schema(): void
    {
        $schema = ['name' => 'string', 'age' => 'int'];
        $attribute = new JsonOutput(schema: $schema);

        $this->assertSame($schema, $attribute->schema);
    }
}
