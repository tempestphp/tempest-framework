<?php

declare(strict_types=1);

use Tempest\View\Renderers\BladeConfig;

return new BladeConfig(
    viewPaths: [
        __DIR__ . '/../views/',
    ],
    cachePath: __DIR__ . '/../views/cache/',
);
