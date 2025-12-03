<?php

namespace Tempest\Testing\Actions;

use Psr\Container\ContainerInterface;
use Tempest\Container\Container;
use Tempest\Container\Singleton;
use Tempest\Reflection\MethodReflector;
use Tempest\Testing\Events\TestAfterExecuted;
use Tempest\Testing\Events\TestBeforeExecuted;
use Tempest\Testing\Events\TestFailed;
use Tempest\Testing\Events\TestFinished;
use Tempest\Testing\Events\TestStarted;
use Tempest\Testing\Events\TestSucceeded;
use Tempest\Testing\Exceptions\InvalidProviderData;
use Tempest\Testing\Exceptions\TestHasFailed;
use Tempest\Testing\Test;

use function Tempest\event;

#[Singleton]
final class RunTest
{
    public function __construct(
        private ContainerInterface|Container $container,
    ) {}

    public function __invoke(Test $test): void
    {
        $instance = $this->getInstance($test);

        $providedData = [];

        foreach ($test->provide ?? [[]] as $provider) {
            if (is_array($provider)) {
                $providedData[] = $provider;
                continue;
            }

            if (is_string($provider)) {
                if (! method_exists($instance, $provider)) {
                    throw InvalidProviderData::invalidMethodName($test, $provider);
                }

                $provider = $instance->{$provider}(...);
            }

            if (is_callable($provider)) {
                // TODO: add DI here as well?
                $provider = $provider();
            }

            if (is_iterable($provider)) {
                $providedData = [...$providedData, ...iterator_to_array($provider)];
            }
        }

        foreach ($providedData as $data) {
            $this->runEntry($test, $instance, $data);
        }
    }

    private function runEntry(Test $test, object $instance, array $data): void
    {
        event(new TestStarted($test->name));

        try {
            $this->runBefore($test, $instance);

            $this->callMethod($instance, $test->handler, $data);

            $this->runAfter($test, $instance);

            event(new TestSucceeded($test->name));
        } catch (TestHasFailed $exception) {
            $this->runAfter($test, $instance);

            event(TestFailed::fromException($test->name, $exception));
        }

        event(new TestFinished($test->name));
    }

    private function runBefore(Test $test, object $instance): void
    {
        foreach ($test->before as $before) {
            $this->callMethod($instance, $before);

            event(new TestBeforeExecuted($test, $before));
        }
    }

    private function runAfter(Test $test, object $instance): void
    {
        foreach ($test->after as $after) {
            $this->callMethod($instance, $after);

            event(new TestAfterExecuted($test, $after));
        }
    }

    private function getInstance(Test $test): object
    {
        return $this->container->get($test->handler->getDeclaringClass()->getName());
    }

    private function callMethod(object $instance, MethodReflector $method, array $data = []): void
    {
        foreach ($method->getParameters() as $parameter) {
            if (isset($data[$parameter->getName()])) {
                continue;
            }

            if ($parameter->hasDefaultValue()) {
                continue;
            }

            if ($parameter->getType()->isScalar()) {
                continue;
            }

            $data[$parameter->getName()] = $this->container->get($parameter->getType()->getName());
        }

        $instance->{$method->getName()}(...$data);
    }
}
