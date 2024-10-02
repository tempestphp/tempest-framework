<?php

declare(strict_types=1);

namespace Tempest\Debug;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\VarDumper;
use Tempest\Container\GenericContainer;
use Tempest\Highlight\Themes\TerminalStyle;
use Tempest\Log\LogConfig;

final readonly class Debug
{
    private function __construct(private ?LogConfig $logConfig = null)
    {
    }

    public static function resolve(): self
    {
        if (! class_exists(GenericContainer::class)) {
            return new self();
        }

        if (! class_exists(LogConfig::class)) {
            return new self();
        }

        $container = GenericContainer::instance();

        $logConfig = $container?->get(LogConfig::class);

        return new self($logConfig);
    }

    public function log(array $items, bool $writeToLog = true, bool $writeToOut = true): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $callPath = $trace[1]['file'] . ':' . $trace[1]['line'];

        if ($writeToLog) {
            $this->writeToLog($items, $callPath);
        }

        if ($writeToOut) {
            $this->writeToOut($items, $callPath);
        }
    }

    private function writeToLog(array $items, string $callPath): void
    {
        if ($this->logConfig === null) {
            return;
        }

        if (! $this->logConfig->debugLogPath) {
            return;
        }

        $handle = @fopen($this->logConfig->debugLogPath, 'a');

        if (! $handle) {
            return;
        }

        foreach ($items as $key => $item) {
            $output = $this->createDump($item) . $callPath;

            fwrite($handle, "{$key} " . $output . PHP_EOL);
        }

        fclose($handle);
    }

    private function writeToOut(array $items, string $callPath): void
    {
        foreach ($items as $key => $item) {
            if (defined('STDOUT')) {
                fwrite(STDOUT, TerminalStyle::BG_BLUE(" {$key} ") . ' ');

                $output = $this->createDump($item);

                fwrite(STDOUT, $output);

                fwrite(STDOUT, $callPath . PHP_EOL);
            } else {
                echo sprintf(
                    '<span style="
                    display:inline-block; 
                    color: #fff; 
                    font-family: %s;
                    padding: 2px 4px;
                    font-size: 0.8rem;
                    margin-bottom: -12px;
                    background: #0071BC;"
                >%s (%s)</span>',
                    'Source Code Pro, ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, Liberation Mono, Courier New, monospace',
                    $key,
                    $callPath,
                );

                VarDumper::dump($item);
            }
        }
    }

    private function createDump(mixed $input): string
    {
        $cloner = new VarCloner();

        $output = '';

        $dumper = new CliDumper(function ($line, $depth) use (&$output): void {
            if ($depth < 0) {
                return;
            }

            $output .= str_repeat(' ', $depth) . $line . "\n";
        });

        $dumper->setColors(true);

        $dumper->dump($cloner->cloneVar($input));

        return preg_replace(
            pattern: '/\e](.*)\e]8;;\e/',
            replacement: '',
            subject: $output,
        );
    }
}
