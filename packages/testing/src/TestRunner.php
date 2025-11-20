<?php

namespace Tempest\Testing;

use Tempest\Container\Container;
use Tempest\Testing\Events\TestFailed;
use Tempest\Testing\Events\TestSkipped;
use Tempest\Testing\Events\TestSucceeded;
use Tempest\Testing\Exceptions\TestHasFailed;
use function Tempest\event;

final readonly class TestRunner
{
    public function __construct(
        private string $name,
        private ?string $filter,
    ) {}

    /** @param \Tempest\Reflection\MethodReflector[] $tests */
    public function run(Container $container, array $tests): TestRunnerResult
    {
        $result = new TestRunnerResult();

        foreach ($tests as $test) {
            $testName = $test->getDeclaringClass()->getName() . ':' . $test->getName();

            if ($this->filter && ! str_contains($testName, $this->filter)) {
                event(new TestSkipped($testName));

                continue;
            }

            try {
                $container->invoke($test);

                event(new TestSucceeded($testName));

                $result->success();
            } catch (TestHasFailed $testHasFailed) {
                event(new TestFailed($testName, $testHasFailed));

                $result->fail();
            }
        }

        return $result;
    }
}