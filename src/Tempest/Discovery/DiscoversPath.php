<?php

declare(strict_types=1);

namespace Tempest\Discovery;

interface DiscoversPath
{
    public function discoverPath(string $path): void;
}
