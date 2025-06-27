<?php

namespace Tempest\Icon;

use Exception;

final readonly class IconDownloaded
{
    public function __construct(
        public string $collection,
        public string $name,
        public string $icon,
    ) {}
}
