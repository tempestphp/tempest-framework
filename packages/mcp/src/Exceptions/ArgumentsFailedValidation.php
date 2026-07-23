<?php

declare(strict_types=1);

namespace Tempest\Mcp\Exceptions;

use Exception;

final class ArgumentsFailedValidation extends Exception implements McpException
{
    public function __construct(
        /** @var array<string, string[]> */
        public readonly array $failures,
    ) {
        parent::__construct(implode("\n", array_merge(...array_values($failures))));
    }
}
