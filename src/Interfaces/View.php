<?php

declare(strict_types=1);

namespace Tempest\Interfaces;

use Tempest\AppConfig;

interface View
{
    public function render(AppConfig $appConfig): string;

    public function path(string $path): self;

    public function data(...$params): self;

    public function extends(string $path, ...$params): self;

    public function raw(string $name): ?string;
}
