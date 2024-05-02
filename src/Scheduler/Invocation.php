<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

interface Invocation
{
    public function getName(): string;
}
