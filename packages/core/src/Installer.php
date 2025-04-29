<?php

declare(strict_types=1);

namespace Tempest\Core;

interface Installer
{
    public string $name {
        get;
    }

    public function install(): void;
}
