<?php

declare(strict_types=1);

use Tempest\View\Renderers\TwigConfig;

return new TwigConfig(
    viewPaths: [
        __DIR__ . '/../views/',
    ],
    cachePath: __DIR__ . '/../views/cache/',
);
