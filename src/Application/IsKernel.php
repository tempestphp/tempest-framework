<?php

namespace Tempest\Application;

use RuntimeException;
use Tempest\Container\Container;

trait IsKernel
{
    private Container $container;

    private string $basePath;

    private array $bootstrappers = [];

    private array $configurationPaths = [];

    private bool $isBooted = false;

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

    public function getBootstrappers(): array
    {
        return $this->bootstrappers;
    }

    public function setBootstrappers(array $bootstrappers): self
    {
        $this->bootstrappers = [];

        foreach ($this->bootstrappers as $bootstrapper) {
            $this->addBootstrapper($bootstrapper);
        }

        return $this;
    }

    public function addBootstrapper(BootstrapsKernel $bootstrapper): self
    {
        $this->bootstrappers[] = $bootstrapper;

        return $this;
    }

    public function getConfigurationPaths(): array
    {
        return $this->configurationPaths;
    }

    public function setConfigurationPaths(array $configurationPaths): self
    {
        $this->configurationPaths = [];

        foreach ($configurationPaths as $configurationPath) {
            $this->addConfigurationPath($configurationPath);
        }

        return $this;
    }

    public function addConfigurationPath(string $path): self
    {
        if (! is_dir($path)) {
            // TODO: Make this more helpful.
            throw new RuntimeException('The config path does not exist.');
        }

        $this->configurationPaths[] = $path;

        return $this;
    }

    public function boot(): void
    {
        if ($this->isBooted) {
            return;
        }

        foreach ($this->bootstrappers as $bootstrapper) {
            $this->container->get($bootstrapper)->bootstrap($this);
        }
    }

    public function shutdown(): void
    {
        // TODO: Implement this.
    }
}