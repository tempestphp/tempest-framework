<?php

declare(strict_types=1);

namespace Tempest\Application;

use RuntimeException;
use Tempest\Application\Bootstrap\LoadConfigurations;
use Tempest\Application\Bootstrap\LoadEnvironmentVariables;
use Tempest\Container\Container;

final class ConsoleKernel implements Kernel
{
    private Container $container;

    private string $basePath;

    private array $configPaths = [];

    /**
     * @var array<array-key, class-string<BootstrapsKernel>>
     */
    private array $bootstrappers = [
        LoadEnvironmentVariables::class,
        LoadConfigurations::class,
    ];

    public function __construct(Container $container, string $basePath)
    {
        $this->setContainer($container);
        $this->setBasePath($basePath);
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function setContainer(Container $container): self
    {
        $this->container = $container;

        return $this;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function setBasePath(string $path): self
    {
        if (! is_dir($path)) {
            // TODO: Make this more helpful.
            throw new RuntimeException('The root directory does not exist.');
        }

        $this->basePath = realpath($path);

        return $this;
    }

    public function getConfigurationPaths(): array
    {
        return $this->configPaths;
    }

    public function addConfigurationPath(string $path): self
    {
        if (! is_dir($path)) {
            // TODO: Make this more helpful.
            throw new RuntimeException('The config path does not exist.');
        }

        $this->configPaths[] = $path;

        return $this;
    }

    public function boot(): void
    {
        foreach ($this->bootstrappers as $bootstrapper) {
            $this->container->get($bootstrapper)->bootstrap($this);
        }
    }

    public function run(): void
    {
        $this->boot();

        // TODO: Handle stuff.

        $this->shutdown();
    }

    public function shutdown(): void
    {
        // TODO: Implement shutdown() method.
    }
}
