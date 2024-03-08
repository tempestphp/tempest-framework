<?php

declare(strict_types=1);

namespace Tempest\Application;

use RuntimeException;
use Tempest\Application\Bootstrap\LoadEnvironmentVariables;
use Tempest\Container\Container;

final class HttpKernel implements Kernel
{
    private Container $container;

    private string $rootDirectory;

    /**
     * @var array<array-key, class-string<BootstrapsKernel>>
     */
    private array $bootstrappers = [
        LoadEnvironmentVariables::class,
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

    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    public function getBasePath(): string
    {
        return $this->rootDirectory;
    }

    public function setBasePath(string $path): void
    {
        if (! is_dir($path)) {
            // TODO: Make this more helpful.
            throw new RuntimeException('The root directory does not exist.');
        }

        $this->rootDirectory = realpath($path);
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
        dump('Running application...');
        dump($_ENV);

        $this->shutdown();
    }

    public function shutdown(): void
    {
        // TODO: Implement shutdown() method.
    }
}
