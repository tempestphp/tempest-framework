<?php

declare(strict_types=1);

namespace Tempest\Vite;

final readonly class ViteBridgeFile
{
    public function __construct(
        public string $url,
    ) {
    }
}
