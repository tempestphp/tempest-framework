<?php

namespace Tempest;

use Tempest\Container\GenericContainer;
use Tempest\Interfaces\Container;
use Tempest\Interfaces\Request;
use Tempest\Interfaces\Router;
use Tempest\Interfaces\Server;
use Tempest\Http\GenericRequest;
use Tempest\Http\GenericRouter;
use Tempest\Http\RequestResolver;

final readonly class Kernel
{
    public function init(string $rootDirectory): Container
    {
        $container = $this->registerContainer();

        $this->initConfig($rootDirectory, $container);

        return $container;
    }

    public function registerContainer(): Container
    {
        $container = new GenericContainer();

        $container
            ->singleton(Kernel::class, fn() => $this)
            ->singleton(Container::class, fn() => $container)
            ->singleton(Router::class, fn(Container $container) => $container->get(GenericRouter::class))
            ->addResolver(new RequestResolver())
        ;

        return $container;
    }

    private function initConfig(string $rootDirectory, Container $container): void
    {
        $configFiles = glob(path($rootDirectory, 'Config/**.php'));

        foreach ($configFiles as $configFile) {
            $container->config(require $configFile);
        }
    }
}