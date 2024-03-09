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

    public function setContainer(Container $container): self;

    public function getBasePath(): string;

    public function setBasePath(string $path): self;

    /**
     * @return array<array-key,string>
     */
    public function getConfigurationPaths(): array;

    public function addConfigurationPath(string $path): self;

    public function boot(): void;

    public function run(): void;

    public function shutdown(): void;
}
