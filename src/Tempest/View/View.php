<?php

declare(strict_types=1);

namespace Tempest\View;

interface View
{
    public function getPath(): string;

    public function getData(): array;

    public function getRawData(): array;

    public function getRaw(string $key): mixed;

    public function get(string $key): mixed;

    public function data(...$params): self;

    public function raw(string $name): ?string;

    public function eval(string $eval): mixed;
}
