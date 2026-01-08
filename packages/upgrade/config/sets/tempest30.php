<?php

use Rector\Config\RectorConfig;
use Tempest\Upgrade\Tempest3\UpdateCommandFunctionImportsRector;
use Tempest\Upgrade\Tempest3\UpdateEventFunctionImportsRector;
use Tempest\Upgrade\Tempest3\UpdateExceptionProcessorRector;
use Tempest\Upgrade\Tempest3\UpdateHasContextRector;
use Tempest\Upgrade\Tempest3\UpdateMapperFunctionImportsRector;
use Tempest\Upgrade\Tempest3\UpdateReflectionFunctionImportsRector;
use Tempest\Upgrade\Tempest3\UpdateViewFunctionImportsRector;

return static function (RectorConfig $config): void {
    $config->importNames();
    $config->importShortClasses();

    $config->rule(UpdateCommandFunctionImportsRector::class);
    $config->rule(UpdateEventFunctionImportsRector::class);
    $config->rule(UpdateMapperFunctionImportsRector::class);
    $config->rule(UpdateReflectionFunctionImportsRector::class);
    $config->rule(UpdateViewFunctionImportsRector::class);
    $config->rule(UpdateExceptionProcessorRector::class);
    $config->rule(UpdateHasContextRector::class);
};
