<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Renderers;

use Tempest\Console\Components\ComponentState;
use Tempest\Console\Terminal\Terminal;
use function Tempest\Support\str;

final class TaskRenderer
{
    use RendersInput;

    public function __construct(
        private readonly SpinnerRenderer $spinner,
        private readonly string $label,
    ) {
    }

    public function render(Terminal $terminal, ComponentState $state, float $startedAt, ?float $finishedAt, ?string $hint = null): string
    {
        $this->prepareRender($terminal, $state);
        $this->label($this->label);

        $runtime = fn (float $finishedAt) => $finishedAt
            ? number_format(($finishedAt - $startedAt) / 1_000_000, decimals: 0)
            : null;

        $hint = $content ?? match ($this->state) {
            ComponentState::DONE => '<style="fg-gray">Done in <style="bold">'.$runtime($finishedAt).'ms</style>.</style>',
            ComponentState::ERROR => '<style="fg-red">An error occurred.</style>',
            ComponentState::CANCELLED => '<style="fg-yellow">Cancelled.</style>',
            default => $runtime(hrtime(as_number: true)) . 'ms',
        };

        $this->line(
            append: str()
                ->append(match ($this->state) {
                    ComponentState::DONE => '<style="fg-green">✔</style>',
                    ComponentState::ERROR => '<style="fg-red">✖</style>',
                    ComponentState::CANCELLED => '<style="fg-yellow">⚠</style>',
                    default => '<style="fg-gray">'.$this->spinner->render($terminal, $this->state).'</style>',
                })
                ->append('<style="fg-gray"> ', $hint, '</style>'),
        );

        // If a task has an error, it is no longer active.
        if (! $state->isFinished() && $this->state !== ComponentState::ERROR) {
            $this->newLine();
        }

        return $this->finishRender();
    }
}