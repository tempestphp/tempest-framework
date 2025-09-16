<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Tempest\Upgrade\Set\TempestSetList;

return static function (RectorConfig $config): void {
    $config->sets([
        TempestSetList::TEMPEST_20,
    ]);
};
