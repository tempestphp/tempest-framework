<?php

use Rector\Config\RectorConfig;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Tempest\Upgrade\Tempest314\UpdateConnectionImplementationsRector;
use Tempest\Upgrade\Tempest314\UpdateKernelImplementationsRector;

return static function (RectorConfig $config): void {
    SimpleParameterProvider::setParameter(Option::AUTO_IMPORT_NAMES, value: true);
    SimpleParameterProvider::setParameter(Option::IMPORT_SHORT_CLASSES, value: true);

    $config->rule(UpdateConnectionImplementationsRector::class);
    $config->rule(UpdateKernelImplementationsRector::class);
};
