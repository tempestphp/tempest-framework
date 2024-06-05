<?php

declare(strict_types=1);

namespace Tempest\Log;

use Monolog\Handler\HandlerInterface;
use Monolog\Level;
use Monolog\Processor\ProcessorInterface;

interface LogChannel
{
    /**
     * @return array<array-key, HandlerInterface>
     */
    public function getHandlers(Level $level): array;

    /**
     * @return array<array-key, ProcessorInterface>
     */
    public function getProcessors(): array;
}
