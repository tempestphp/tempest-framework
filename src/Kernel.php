<?php

namespace Tempest;

use Tempest\Container\Container;
use Tempest\Interfaces\Container as ContainerInterface;
use Tempest\Interfaces\Router as RouterInterface;
use Tempest\Route\Router;

final readonly class Kernel
{
    public function init(string $rootDirectory): ContainerInterface
    {
        $container = $this->registerContainer();

        $this->initConfig($rootDirectory, $container);

        return $container;
    }

    public function registerContainer(): Container
    {
        $container = new Container();

        $container
            ->singleton(Kernel::class, fn() => $this)
            ->singleton(ContainerInterface::class, fn() => $container)
            ->singleton(RouterInterface::class, fn(ContainerInterface $container) => $container->get(Router::class));

        return $container;
    }

    private function initConfig(string $rootDirectory, ContainerInterface $container): void
    {
        $configFiles = glob(path($rootDirectory, 'Config/**.php'));

        foreach ($configFiles as $configFile) {
            $container->config(require $configFile);
        }
    }
}