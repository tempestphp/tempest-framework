<?php

use Tempest\View\Renderers\TwigConfig;

declare(strict_types=1);

return new TwigConfig(
    viewPaths: [
        __DIR__ . '/../views/',
    ],

    cachePath: __DIR__ . '/../views/cache/',
);