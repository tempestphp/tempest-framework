<?php

declare(strict_types=1);

namespace Tempest\Support\VarExport;

use Stringable;
use Symfony\Component\VarDumper\VarDumper;
use Tempest\Log\LogConfig;

final readonly class Debug
{
    public function __construct(private LogConfig $logConfig)
    {
    }

    public function log(mixed ...$input): void
    {
        $handle = fopen($this->logConfig->debugLogPath, 'a');

        foreach ($input as $key => $item) {
            if ($item instanceof Stringable) {
                $output = (string) $item;
            } else {
                $output = var_export($item, true);
            }

            fwrite($handle, "[{$key}] {$output}" . PHP_EOL);
        }

        fclose($handle);

        VarDumper::dump(...$input);

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        fwrite(STDOUT, PHP_EOL . "Called in " . $trace[1]['file'] . ':' . $trace[1]['line'] . PHP_EOL);
    }
}
