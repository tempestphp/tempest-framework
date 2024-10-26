<?php

declare(strict_types=1);

namespace Tempest\Core;

interface Installer
{
    public function getName(): string;

    public function install(): void;
}
