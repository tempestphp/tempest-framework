<?php

declare(strict_types=1);

namespace Tempest\Support\Namespace;

use Exception;

final class NoMatchingRegisteredNamespaceException extends Exception
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf('No registered namespace matches the specified path `%s`.', $path));
    }
}
