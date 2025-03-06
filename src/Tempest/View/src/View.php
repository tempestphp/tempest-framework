<?php

declare(strict_types=1);

namespace Tempest\View;

interface View
{
    public string $path {
        get;
    }

    public ?string $relativeRootPath {
        get;
    }

    public array $data {
        get;
    }

    public function get(string $key): mixed;

    public function has(string $key): bool;

    public function data(...$params): self;
}
