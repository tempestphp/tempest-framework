<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

final class NullScheduler implements Scheduler
{
    public function run(): void
    {

    }
}
