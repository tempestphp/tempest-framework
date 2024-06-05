<?php

declare(strict_types=1);

namespace Tempest\Support\VarExport;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\VarDumper;
use Tempest\Highlight\Themes\TerminalStyle;
use Tempest\Log\LogConfig;

final readonly class Debug
{
    public function __construct(private LogConfig $logConfig)
    {
    }

    public function log(array $items, bool $writeToLog = true, bool $writeToOut = true): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $callPath = "Called in " . $trace[1]['file'] . ':' . $trace[1]['line'];

        if ($writeToLog) {
            $this->writeToLog($items, $callPath);
        }

        if ($writeToOut) {
            $this->writeToOut($items, $callPath);
        }
    }

    private function writeToLog(array $items, string $callPath): void
    {
        if (! $this->logConfig->debugLogPath) {
            return;
        }

        $handle = @fopen($this->logConfig->debugLogPath, 'a');

        if (! $handle) {
            return;
        }

        $cloner = new VarCloner();

        foreach ($items as $key => $item) {
            $output = '';

            $dumper = new CliDumper(function ($line, $depth) use (&$output) {
                if ($depth < 0) {
                    return;
                }

                $output .= str_repeat(' ', $depth) . $line . "\n";
            });

            $dumper->setColors(true);

            $dumper->dump($cloner->cloneVar($item));

            $output .= $callPath;

            fwrite($handle, "{$key} " . $output . PHP_EOL);
        }

        fclose($handle);
    }

    private function writeToOut(array $items, string $callPath): void
    {
        foreach ($items as $key => $item) {
            if (defined('STDOUT')) {
                fwrite(STDOUT, TerminalStyle::BG_BLUE(" {$key} ") . ' ');
            }

            VarDumper::dump($item);
        }

        if (defined('STDOUT')) {
            fwrite(STDOUT, $callPath . PHP_EOL);
        }
    }
}
