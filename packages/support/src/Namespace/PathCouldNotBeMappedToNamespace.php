<?php

declare(strict_types=1);

namespace Tempest\Support\Namespace;

use Exception;

final class PathCouldNotBeMappedToNamespace extends Exception
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf('The path `%s` could not be mapped to a namespace.', $path));
    }
}
