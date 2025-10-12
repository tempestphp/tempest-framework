<?php

namespace Tempest\Support\Namespace;

final readonly class Psr4Namespace
{
    public function __construct(
        public string $namespace,
        public string $path,
    ) {}
}
