<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Interactive;

use Closure;
use Generator;
use RuntimeException;
use Tempest\Console\Components\ComponentState;
use Tempest\Console\Components\Concerns\HasErrors;
use Tempest\Console\Components\Concerns\HasState;
use Tempest\Console\Components\Renderers\KeyValueRenderer;
use Tempest\Console\Components\Renderers\SpinnerRenderer;
use Tempest\Console\Components\Renderers\TaskRenderer;
use Tempest\Console\InteractiveConsoleComponent;
use Tempest\Console\Terminal\Terminal;
use Throwable;

final class TaskComponent implements InteractiveConsoleComponent
{
    use HasErrors;
    use HasState;

    private KeyValueRenderer $keyValue;

    private TaskRenderer $renderer;

    private int $processId;

    private float $startedAt;

    private ?float $finishedAt = null;

    private(set) array $extensions = ['pcntl'];

    public function __construct(
        private readonly string $label,
        private readonly ?Closure $handler = null,
        private readonly ?string $success = null,
        private readonly ?string $failure = null,
    ) {
        $this->keyValue = new KeyValueRenderer();
        $this->renderer = new TaskRenderer(new SpinnerRenderer(), $label);
        $this->startedAt = hrtime(as_number: true);
    }

    public function render(Terminal $terminal): Generator
    {
        // If there is no task handler, we don't need to fork the process, as
        // it is a time-consuming operation. We can simply consider it done.
        if ($this->handler === null) {
            $this->state = ComponentState::DONE;

            yield $this->renderTask($terminal);

            return true;
        }

        $this->processId = pcntl_fork();

        if ($this->processId === -1) {
            throw new RuntimeException('Could not fork process');
        }

        if (! $this->processId) {
            $this->executeHandler();
        }

        try {
            while (true) {
                // The process is still running, so we continue looping.
                if (pcntl_waitpid($this->processId, $status, flags: WNOHANG) === 0) {
                    yield $this->renderTask($terminal);

                    usleep(80_000);

                    continue;
                }

                // The process is done, we determine its state by its exit code.
                $this->finishedAt = hrtime(as_number: true);
                $this->state = match (pcntl_wifexited($status)) {
                    true => match (pcntl_wexitstatus($status)) {
                        0 => ComponentState::DONE,
                        default => ComponentState::ERROR,
                    },
                    default => ComponentState::CANCELLED,
                };

                yield $this->renderTask($terminal);

                return $this->state === ComponentState::DONE;
            }
        } finally {
            if ($this->state->isFinished() && $this->processId) {
                $this->kill();
            }
        }
    }

    private function renderTask(Terminal $terminal): string
    {
        return $this->renderer->render(
            terminal: $terminal,
            state: $this->state,
            startedAt: $this->startedAt,
            finishedAt: $this->finishedAt,
            hint: '...',
        );
    }

    private function kill(): void
    {
        posix_kill($this->processId, SIGTERM);
    }

    private function executeHandler(): void
    {
        try {
            exit((int) (($this->handler ?? static fn (): bool => true)() === false));
        } catch (Throwable) {
            exit(1);
        }
    }

    public function renderFooter(Terminal $terminal): ?string
    {
        return null;
    }
}
