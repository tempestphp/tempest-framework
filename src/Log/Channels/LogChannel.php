<?php

declare(strict_types=1);

namespace Tempest\Log\Channels;

use Monolog\Handler\HandlerInterface;
use Monolog\Level;
use Monolog\Processor\ProcessorInterface;

interface LogChannel
{
    /**
     * @param Level $level
     * @param array $config
     *
     * @return array<int, HandlerInterface>|HandlerInterface
     */
    public function handler(Level $level, array $config): array|HandlerInterface;

    /**
     * @return ProcessorInterface|array<int, ProcessorInterface>
     */
    public function processor(array $config): array|ProcessorInterface;
}
