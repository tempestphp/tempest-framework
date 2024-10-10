<?php

declare(strict_types=1);

namespace Tempest\Core;

final readonly class ComposerNamespace
{
    public function __construct(
        public string $namespace,
        public string $path,
    ) {
    }
}
