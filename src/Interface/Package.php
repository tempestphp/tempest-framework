<?php

declare(strict_types=1);

namespace Tempest\Interface;

interface Package
{
    public function getPath(): string;

    public function getNamespace(): string;

    public function getDiscovery(): array;
}
