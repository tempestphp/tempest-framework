<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Interactive;

use Closure;
use Generator;
use Tempest\Console\Components\Concerns\HasErrors;
use Tempest\Console\Components\Concerns\HasState;
use Tempest\Console\Components\Concerns\RendersControls;
use Tempest\Console\Components\Static\StaticProgressBarComponent;
use Tempest\Console\HasStaticComponent;
use Tempest\Console\InteractiveConsoleComponent;
use Tempest\Console\StaticConsoleComponent;
use Tempest\Console\Terminal\Terminal;

final class ProgressBarComponent implements InteractiveConsoleComponent, HasStaticComponent
{
    use HasErrors;
    use HasState;
    use RendersControls;

    public function __construct(
        private readonly iterable $data,
        private readonly Closure $handler,
        /** @var null|Closure(int $step, int $count): string $format */
        private readonly ?Closure $format = null,
    ) {
    }

    public function render(Terminal $terminal): Generator
    {
        $result = [];

        $count = iterator_count($this->data);
        $step = 1;

        $format = $this->format ?? function (int $step, int $count): string {
            $width = 30;

            $progress = (int) round(($step / $count) * $width);

            if ($step === $count) {
                $bar = sprintf(
                    '[%s]',
                    str_repeat('=', $width),
                );
            } else {
                $bar = sprintf(
                    '[%s>%s]',
                    str_repeat('=', max(0, $progress)),
                    str_repeat(' ', $width - $progress),
                );
            }

            return sprintf(
                '%s (%s/%s)',
                $bar,
                $step,
                $count,
            );
        };

        foreach ($this->data as $item) {
            yield $format($step, $count);

            $result[] = ($this->handler)($item);

            $step += 1;
        }

        return $result;
    }

    public function getStaticComponent(): StaticConsoleComponent
    {
        return new StaticProgressBarComponent(
            data: $this->data,
            handler: $this->handler,
            format: $this->format,
        );
    }

    private function getControls(): array
    {
        return [
            'ctrl+c' => 'cancel',
        ];
    }
}
