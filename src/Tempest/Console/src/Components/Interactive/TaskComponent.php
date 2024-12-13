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
use Tempest\Console\InteractiveConsoleComponent;
use Tempest\Console\Terminal\Terminal;
use Throwable;
use function Tempest\Support\str;

final class TaskComponent implements InteractiveConsoleComponent
{
    use HasErrors;
    use HasState;

    private SpinnerRenderer $spinner;

    private KeyValueRenderer $keyValue;

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
        $this->spinner = new SpinnerRenderer();
        $this->keyValue = new KeyValueRenderer();
        $this->startedAt = hrtime(as_number: true);
    }

    public function render(Terminal $terminal): Generator
    {
        if ($this->handler === null) {
            $this->state = ComponentState::DONE;

            yield $this->renderLine($terminal);

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
                if (pcntl_waitpid($this->processId, $status, flags: WNOHANG) === 0) {
                    yield $this->renderLine($terminal);

                    usleep($this->spinner->speed);

                    continue;
                }

                $this->finishedAt = hrtime(as_number: true);
                $this->state = match (pcntl_wifexited($status)) {
                    true => match (pcntl_wexitstatus($status)) {
                        0 => ComponentState::DONE,
                        default => ComponentState::ERROR,
                    },
                    default => ComponentState::CANCELLED,
                };

                yield $this->renderLine($terminal);

                return $this->state === ComponentState::DONE;
            }
        } finally {
            if ($this->state !== ComponentState::DONE && $this->processId) {
                $this->kill();
            }
        }
    }

    private function renderLine(Terminal $terminal): string
    {
        $runtime = $this->finishedAt
            ? number_format(($this->finishedAt - $this->startedAt) / 1_000_000, decimals: 0)
            : null;

        $line = $this->keyValue->render(
            key: str()
                ->append(match ($this->state) {
                    ComponentState::DONE => '<style="fg-green">✔</style>',
                    ComponentState::ERROR => '<style="fg-red">✖</style>',
                    ComponentState::CANCELLED => '<style="fg-yellow">⚠</style>',
                    default => $this->spinner->render($terminal, $this->state),
                })
                ->append(' ', $this->label),
            value: $this->state !== ComponentState::ACTIVE
                ? sprintf(
                    '<style="fg-gray">%s</style><style="bold %s">%s</style>',
                    $runtime ? ($runtime . 'ms ') : '',
                    match ($this->state) {
                        ComponentState::DONE => 'fg-green',
                        default => 'fg-red',
                    },
                    match ($this->state) {
                        ComponentState::DONE => $this->success ?? 'DONE',
                        default => $this->failure ?? 'FAIL',
                    },
                )
                : null,
        );

        return $line . ($this->finishedAt ? '' : "\n");
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
