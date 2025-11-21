<?php

namespace Tempest\Testing;

use Symfony\Component\Process\Process;
use function Tempest\event;

final readonly class TestRunner
{
    public function __construct(
        private string $name,
        private ?string $filter,
    ) {}

    /** @param \Tempest\Testing\Test[] $tests */
    public function run(array $tests): TestRunnerResult
    {
        $result = new TestRunnerResult();

        $tests = array_map(
            fn (Test $test) => '--tests="' . $test->name,
            $tests
        );

        $process = new Process([
            PHP_BINDIR . '/php',
            'tempest',
            'test:run',
            ...$tests
        ]);

        $process->start(function (string $type, string $buffer) {
            foreach (explode("\n", trim($buffer)) as $line) {
                $event = unserialize($line);
                event($event);
            }
        });


        $process->wait();

        return $result;
    }
}