<?php

declare(strict_types=1);

namespace Tempest\Mcp;

use Tempest\Reflection\MethodReflector;

final readonly class ToolDefinition
{
    public function __construct(
        public string $name,
        public ?string $description,
        public string $class,
        public MethodReflector $handler,
    ) {}
}
