<?php

declare(strict_types=1);

namespace Tempest\View;

interface View
{
    public function getPath(): string;

    public function getData(): array;

    public function get(string $key): mixed;

    public function has(string $key): bool;

    public function data(...$params): self;
}
