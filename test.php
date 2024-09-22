<?php

require 'vendor/autoload.php';

$filesystem = new Tempest\Filesystem\LocalFilesystem();

$filesystem->traverseDirectory(__DIR__ . '/src', function (SplFileInfo $file) {
    echo $file->getPathname();
}, false);