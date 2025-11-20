<?php

namespace Tempest\Testing;

use Symfony\Component\Process\Process;
use Tempest\Container\Container;
use Tempest\Reflection\MethodReflector;
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

        // TODO: filter before passing to runner
//        if ($filter && ! str_contains($testName, $filter)) {
//            $this->output(new TestSkipped($testName));
//
//            continue;
//        }

        $tests = array_map(
            fn (MethodReflector $test) => '--tests="' . $test->getDeclaringClass()->getName() . '::' . $test->getName() . '"',
            $tests
        );

        $process = new Process([
            PHP_BINDIR . '/php',
            'tempest',
            'test:run',
            ...$tests
        ]);

        $process->start(function (string $type, string $buffer) {
            $event = unserialize(trim($buffer));

            event($event);
        });


        $process->wait();
//
//        foreach ($tests as $test) {
//            $testName = $test->getDeclaringClass()->getName() . '::' . $test->getName();
//
//            if ($this->filter && ! str_contains($testName, $this->filter)) {
//                event(new TestSkipped($testName));
//
//                continue;
//            }
//
//            try {
//                $container->invoke($test);
//
//                event(new TestSucceeded($testName));
//
//                $result->success();
//            } catch (TestHasFailed $exception) {
//                event(TestFailed::fromException($testName, $exception));
//
//                $result->fail();
//            }
//        }

        return $result;
    }
}