<?php

namespace Tempest\Testing\Actions;

use Tempest\Support\Arr\ImmutableArray;
use Tempest\Testing\Events\TestRunEnded;
use Tempest\Testing\Events\TestRunStarted;
use Tempest\Testing\Events\TestsChunked;
use Tempest\Testing\TestRunner;
use function Tempest\event;

final class ChunkAndRunTests
{
    public function __invoke(ImmutableArray $tests, int $processes): void
    {
        $chunks = ceil($tests->count() / $processes);

        $tests = $tests
            ->chunk($chunks)
            ->map(fn (ImmutableArray $tests, int $i) => new TestRunner($i)->run($tests));

        event(new TestsChunked($tests->count()));

        event(new TestRunStarted());

        $tests->map(fn (TestRunner $runner) => $runner->wait());

        event(new TestRunEnded());
    }
}