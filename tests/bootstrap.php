<?php

declare(strict_types=1);

require_once  __DIR__ . '/../vendor/autoload.php';


$script = 'tempest discovery:clear';

// Adjust the command for the OS
if (PHP_OS_FAMILY === 'Windows') {
    // On Windows, use "php" executable and provide the relative path
    $command = "php {$script}";
} else {
    // On Unix-based systems, execute the script directly
    $command = "./{$script}";
}

passthru($command);
