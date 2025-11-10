<?php

use Rector\Config\RectorConfig;
use Tempest\Upgrade\Set\TempestSetList;

return RectorConfig::configure()
    ->withSets([
        TempestSetList::TEMPEST_28,
    ]);
