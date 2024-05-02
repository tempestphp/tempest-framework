<?php

declare(strict_types=1);

namespace Tempest
{
    use Tempest\Container\GenericContainer;
    use Tempest\Events\EventBus;

    /**
     * @template TClassName
     * @param class-string<TClassName> $className
     * @return TClassName
     */
    function get(string $className): object
    {
        $container = GenericContainer::instance();

        return $container->get($className);
    }

    function event(object $event): void
    {
        $eventBus = get(EventBus::class);

        $eventBus->dispatch($event);
    }
}
