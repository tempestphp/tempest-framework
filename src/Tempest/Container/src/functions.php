<?php

declare(strict_types=1);

namespace Tempest {

    use Tempest\Container\GenericContainer;
    use Tempest\Reflection\FunctionReflector;
    use Tempest\Reflection\MethodReflector;

    /**
     * @template TClassName of object
     * @param class-string<TClassName> $className
     * @return TClassName
     */
    function get(string $className, ?string $tag = null, mixed ...$params): object
    {
        $container = GenericContainer::instance();

        return $container->get($className, $tag, ...$params);
    }

    function invoke(MethodReflector|FunctionReflector|callable|string $callable, mixed ...$params): mixed
    {
        $container = GenericContainer::instance();

        return $container->invoke($callable, ...$params);
    }
}
