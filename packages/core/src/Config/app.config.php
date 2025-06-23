<?php

declare(strict_types=1);

use Tempest\Core\AppConfig;

use function Tempest\env;

return new AppConfig(
    name: env('APPLICATION_NAME'),
    baseUri: env('BASE_URI'),
);
