<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Renderers;

use function Tempest\Support\arr;
use function Tempest\Support\str;

final class InstructionsRenderer
{
    private const int MAX_WIDTH = 175;

    public function render(string|array $lines): string
    {
        $lines = arr($lines)
            ->filter()
            ->flatMap(fn (string $string) => str($string)->chunk(self::MAX_WIDTH)->toArray())
            ->toArray();

        $text = str(PHP_EOL);

        foreach ($lines as $line) {
            $text = $text->append('  ', '<style="bold fg-green">│</style>', ' ', trim($line), PHP_EOL);
        }

        return $text->append(PHP_EOL)->toString();
    }
}
