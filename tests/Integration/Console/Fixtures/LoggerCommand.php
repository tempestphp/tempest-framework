<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tempest\Console\ConsoleCommand;
use Tempest\Log\Logger;

final readonly class LoggerCommand
{
    public function __construct(
        private Logger $logger,
    ) {}

    #[ConsoleCommand('logger')]
    public function __invoke(): void
    {
        $this->logger->info('From the log');
    }
}
