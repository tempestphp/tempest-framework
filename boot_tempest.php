<?php

use Tempest\Core\FrameworkKernel;

/** @var \Composer\Autoload\ClassLoader $classLoader */
$classLoader = require_once getcwd() . '/vendor/autoload.php';

$start = microtime(true);

$kernel = FrameworkKernel::boot(
    root: __DIR__,
);

$end = microtime(true);

$elapsed = round(($end - $start) * 1000, 2);
$memory = round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB';

echo $elapsed . 'ms — ' . $memory . PHP_EOL;

//$consoleConfig = $kernel->container->get(ConsoleConfig::class);
//
//if ($consoleConfig->commands !== []) {
//    echo 'Done!' . PHP_EOL;
//} else {
//    echo 'Invalid discovery' . PHP_EOL;
//}
