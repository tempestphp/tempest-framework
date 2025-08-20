<?php

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\RenameProperty;

return static function (RectorConfig $config) : void {
    $config->removeUnusedImports();
    $config->importNames();
    $config->importShortClasses();

    $config->ruleWithConfiguration(RenameClassRector::class, [
        'Tempest\\Database\\Id' => 'Tempest\\Database\\PrimaryKey',
        'Tempest\\CommandBus\\AsyncCommand' => 'Tempest\\CommandBus\\Async',
    ]);

    $config->ruleWithConfiguration(RenamePropertyRector::class, [
        new RenameProperty(
            type: 'Tempest\\Database\\PrimaryKey',
            oldProperty: 'id',
            newProperty: 'value'
        )
    ]);
};