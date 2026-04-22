<?php

use Rector\Config\RectorConfig;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Tempest\Upgrade\Tempest310\UpdatePriorityImportsRector;

return static function (RectorConfig $config): void {
    SimpleParameterProvider::setParameter(Option::AUTO_IMPORT_NAMES, value: true);
    SimpleParameterProvider::setParameter(Option::IMPORT_SHORT_CLASSES, value: true);

    $config->rule(UpdatePriorityImportsRector::class);
};
