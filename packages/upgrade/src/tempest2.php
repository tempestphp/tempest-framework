<?php

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;

return static function (RectorConfig $config) : void {
    $config->removeUnusedImports();
    $config->importNames();
    $config->importShortClasses();
    $config->ruleWithConfiguration(RenameClassRector::class, [
        'Tempest\\Database\\Id' => 'Tempest\\Database\\PrimaryKey',
        'Tempest\\CommandBus\\AsyncCommand' => 'Tempest\\CommandBus\\Async',
    ]);
};