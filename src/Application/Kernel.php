<?php

declare(strict_types=1);

namespace Tempest\Application;

use Tempest\Container\Container;

/**
 * The kernel handles the actual bootup of the application. It registers
 * just the essential services for loading the rest of the application.
 */
interface Kernel
{
    public function getContainer(): Container;

    public function setContainer(Container $container): void;

    public function getRootDirectory(): string;

    public function setRootDirectory(string $path): void;

    public function boot(): void;

    public function run(): void;

    public function shutdown(): void;
}
