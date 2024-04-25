<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

interface Scheduler
{
    public function run(): void;
}
