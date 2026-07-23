<?php

declare(strict_types=1);

namespace Tempest\Mcp\Exceptions;

use Exception;

final class ResourceTemplateWasInvalid extends Exception implements McpException
{
    public function __construct(
        public readonly string $uri,
        public readonly string $variable,
        string $class,
        string $method,
    ) {
        parent::__construct("The resource URI template `{$uri}` on `{$class}::{$method}` uses the variable `{$variable}`, but the method has no matching parameter.");
    }
}
