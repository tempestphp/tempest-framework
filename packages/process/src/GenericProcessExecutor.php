<?php

namespace Tempest\Process;

use Symfony\Component\Process\Process as SymfonyProcess;
use Tempest\Support\Arr\ImmutableArray;

final class GenericProcessExecutor implements ProcessExecutor
{
    public function start(array|string|PendingProcess $command): InvokedSystemProcess
    {
        $pending = $this->createPendingProcess($command);
        $command = $this->createSymfonyProcess($pending);
        $command->start();

        return new InvokedSystemProcess($command);
    }

    public function run(array|string|PendingProcess $command): ProcessResult
    {
        return $this->start($command)->wait();
    }

    public function pool(iterable $pool): Pool
    {
        return new Pool(
            pendingProcesses: new ImmutableArray($pool)->map($this->createPendingProcess(...)),
            processExecutor: $this,
        );
    }

    public function concurrently(iterable $pool): ProcessPoolResults
    {
        return $this->pool($pool)->start()->wait();
    }

    private function createPendingProcess(array|string|PendingProcess $processOrCommand): PendingProcess
    {
        if ($processOrCommand instanceof PendingProcess) {
            return $processOrCommand;
        }

        return new PendingProcess(command: $processOrCommand);
    }

    private function createSymfonyProcess(PendingProcess $pending): SymfonyProcess
    {
        $process = is_iterable($pending->command)
            ? new SymfonyProcess($pending->command, env: $pending->environment)
            : SymfonyProcess::fromShellCommandline((string) $pending->command, env: $pending->environment);

        $process->setWorkingDirectory((string) ($pending->path ?? getcwd()));
        $process->setTimeout($pending->timeout?->getTotalSeconds());

        if ($pending->idleTimeout) {
            $process->setIdleTimeout($pending->idleTimeout->getTotalSeconds());
        }

        if ($pending->input) {
            $process->setInput($pending->input);
        }

        if ($pending->quietly) {
            $process->disableOutput();
        }

        if ($pending->tty) {
            $process->setTty(true);
        }

        if ($pending->options !== []) {
            $process->setOptions($pending->options);
        }

        return $process;
    }
}
