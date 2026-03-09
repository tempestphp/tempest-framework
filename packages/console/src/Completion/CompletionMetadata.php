<?php

declare(strict_types=1);

namespace Tempest\Console\Completion;

final readonly class CompletionMetadata
{
    public function __construct(
        public array $commands,
    ) {}

    public function findCommand(string $name): ?CompletionCommand
    {
        $command = $this->commands[$name] ?? null;

        return $command instanceof CompletionCommand ? $command : null;
    }
}
