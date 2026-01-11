<?php

use Rector\Config\RectorConfig;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Tempest\Upgrade\Tempest3\UpdateArrMapFunctionRector;
use Tempest\Upgrade\Tempest3\UpdateCommandFunctionImportsRector;
use Tempest\Upgrade\Tempest3\UpdateContainerFunctionImportsRector;
use Tempest\Upgrade\Tempest3\UpdateEventFunctionImportsRector;
use Tempest\Upgrade\Tempest3\UpdateExceptionProcessorRector;
use Tempest\Upgrade\Tempest3\UpdateHasContextRector;
use Tempest\Upgrade\Tempest3\UpdateMapperFunctionImportsRector;
use Tempest\Upgrade\Tempest3\UpdateReflectionFunctionImportsRector;
use Tempest\Upgrade\Tempest3\UpdateViewFunctionImportsRector;

return static function (RectorConfig $config): void {
    SimpleParameterProvider::setParameter(Option::AUTO_IMPORT_NAMES, value: true);
    SimpleParameterProvider::setParameter(Option::IMPORT_SHORT_CLASSES, value: true);

    $config->rule(UpdateArrMapFunctionRector::class);
    $config->rule(UpdateCommandFunctionImportsRector::class);
    $config->rule(UpdateContainerFunctionImportsRector::class);
    $config->rule(UpdateEventFunctionImportsRector::class);
    $config->rule(UpdateMapperFunctionImportsRector::class);
    $config->rule(UpdateReflectionFunctionImportsRector::class);
    $config->rule(UpdateViewFunctionImportsRector::class);
    $config->rule(UpdateExceptionProcessorRector::class);
    $config->rule(UpdateHasContextRector::class);
};
