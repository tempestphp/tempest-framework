<?php

namespace Tempest\Testing\Runner;

use Symfony\Component\Process\Process;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Testing\Test;
use function Tempest\event;

final readonly class TestRunner
{
    public function __construct(private string $name = 'Default') {}

    private Process $process;

    /** @param ImmutableArray<array-key, \Tempest\Testing\Test> $tests */
    public function run(ImmutableArray $tests): self
    {
        $tests = $tests->map(fn (Test $test) => '--tests="' . $test->name . '"');

        $this->process = new Process([
            PHP_BINDIR . '/php',
            'tempest',
            'test:run',
            ...$tests
        ]);

        $this->process->start(function (string $type, string $buffer) {
            foreach (explode(PHP_EOL, trim($buffer)) as $line) {
                if (str_starts_with($line, '[EVENT]')) {
                    $output = unserialize(substr($line, strlen('[EVENT] ')));
                    event($output);
                } else {
                    echo $line . PHP_EOL;
                }
            }
        });

        return $this;
    }

    public function wait(): self
    {
        $this->process->wait();

        return $this;
    }
}