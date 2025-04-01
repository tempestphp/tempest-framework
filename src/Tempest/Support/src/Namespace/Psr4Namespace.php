<?php

namespace Tempest\Support\Namespace;

use function Tempest\Support\Path\is_absolute_path;

final readonly class Psr4Namespace
{
    public function __construct(
        public string $namespace,
        public string $path,
    ) {}
}
