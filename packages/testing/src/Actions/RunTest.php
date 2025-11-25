<?php

namespace Tempest\Testing\Actions;

use Tempest\Container\Container;
use Tempest\Testing\Events\TestFailed;
use Tempest\Testing\Events\TestSucceeded;
use Tempest\Testing\Exceptions\TestHasFailed;
use Tempest\Testing\Test;
use function Tempest\event;

final readonly class RunTest
{
    public function __construct(
        private Container $container
    ) {}

    public function __invoke(Test $test): void
    {
        try {
            $this->container->invoke($test->handler);

            event(new TestSucceeded($test->name));
        } catch (TestHasFailed $exception) {
            event(TestFailed::fromException($test->name, $exception));
        }
    }
}