<?php

declare(strict_types=1);

namespace Tempest\Core;

interface DiscoversPath
{
    public function discoverPath(string $path): void;
}
