<?php

namespace Tempest\Console\Components;

use Closure;
use Generator;
use Tempest\Console\ConsoleComponent;

final readonly class ProgressBarComponent implements ConsoleComponent
{
    public function __construct(
        private iterable $data,
        private Closure $handler,
        private ?Closure $format = null,
    ) {}

    public function render(): Generator
    {
        $result = [];

        $count = iterator_count($this->data);
        $step = 1;

        $format = $this->format ?? function (int $step, int $count): string {
            $width = 30;

            $progress = round(($step / $count) * $width);

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
                ) . PHP_EOL;
        };

        foreach ($this->data as $item) {
            yield $format($step, $count);

            $processed = ($this->handler)($item);
            $result[] = $processed;
            $step += 1;
        }

        return $result;
    }
}