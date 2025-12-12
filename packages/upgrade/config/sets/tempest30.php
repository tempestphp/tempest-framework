<?php

use Rector\Config\RectorConfig;
use Tempest\Upgrade\Tempest3\UpdateMapperFunctionImportsRector;

return static function (RectorConfig $config): void {
    $config->importNames();
    $config->importShortClasses();

    $config->rule(UpdateMapperFunctionImportsRector::class);
};
