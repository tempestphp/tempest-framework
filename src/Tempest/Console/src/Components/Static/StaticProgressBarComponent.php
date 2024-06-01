<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Static;

use Closure;
use Tempest\Console\Console;
use Tempest\Console\StaticComponent;

final readonly class StaticProgressBarComponent implements StaticComponent
{
    public function __construct(
        private iterable $data,
        private Closure $handler,
        /** @var null|Closure(int $step, int $count): string $format */
        private ?Closure $format = null,
    ) {
    }

    public function render(Console $console): array
    {
        $result = [];

        $count = iterator_count($this->data);
        $step = 1;

        $format = $this->format ?? function (int $step, int $count): string {
            $width = 30;

            $progress = (int)round(($step / $count) * $width);

            if ($step === $count) {
                $bar = sprintf(
                    '[%s]',
                    str_repeat('=', $width + 1),
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
            $console->write($format($step, $count));

            $result[] = ($this->handler)($item);

            $step += 1;
        }

        return $result;
    }
}
