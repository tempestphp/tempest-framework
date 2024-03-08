<?php

declare(strict_types=1);

namespace Tempest\Application;

use RuntimeException;
use Tempest\Application\Bootstrap\LoadConfigurations;
use Tempest\Application\Bootstrap\LoadEnvironmentVariables;
use Tempest\Container\Container;
use function Tempest\path;

final class ConsoleKernel implements Kernel
{
    private Container $container;

    private string $rootDirectory;

    private string $configPath;

    /**
     * @var array<array-key, class-string<BootstrapsKernel>>
     */
    private array $bootstrappers = [
        LoadEnvironmentVariables::class,
        LoadConfigurations::class,
    ];

    public function __construct(Container $container, string $rootDirectory)
    {
        $this->setContainer($container);
        $this->setBasePath($rootDirectory);
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
        return $this->rootDirectory;
    }

    public function setBasePath(string $path): self
    {
        if (! is_dir($path)) {
            // TODO: Make this more helpful.
            throw new RuntimeException('The root directory does not exist.');
        }

        $this->rootDirectory = realpath($path);

        return $this;
    }

    public function getConfigPath(): string
    {
        return $this->configPath ??= path(
            $this->rootDirectory, 'config'
        );
    }

    public function setConfigPath(string $path): self
    {
        if (! is_dir($path)) {
            // TODO: Make this more helpful.
            throw new RuntimeException('The config path does not exist.');
        }

        $this->configPath = $path;

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
