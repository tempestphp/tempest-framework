<?php

use Tempest\Application\ConsoleApplication;
use Tempest\Application\Kernel;

$autoloaderPaths = [
    __DIR__ . '/vendor/autoload.php',
    getcwd() . '/vendor/autoload.php',
    getcwd() . '/../autoload.php',
];

$foundAutoloaderPath = null;

foreach ($autoloaderPaths as $autoloaderPath) {
    if (file_exists($autoloaderPath)) {
        require_once $autoloaderPath;
        $foundAutoloaderPath = $autoloaderPath;
        break;
    }
}

if (! $foundAutoloaderPath) {
    throw new Exception("Could not find autoload.php");
}

$appPaths = [
    __DIR__ . '/app/',
    getcwd() . '/app/',
    __DIR__ . '/src/',
    getcwd() . '/src/',
];

$foundAppPath = null;

foreach ($appPaths as $appPath) {
    if (is_dir($appPath)) {
        $foundAppPath = $appPath;
        break;
    }
}

if (! $foundAppPath) {
    throw new Exception("Could not locate app directory.");
}

$container = (new Kernel())->init(
    rootDirectory: $foundAppPath,
    rootNamespace: 'App\\',
);

$app = new ConsoleApplication(
    args: $_SERVER['argv'],
    container: $container,
);

$app->run();

exit;