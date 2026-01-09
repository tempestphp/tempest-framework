<?php

declare(strict_types=1);

namespace Tempest\Debug;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\VarDumper;
use Tempest\Container\GenericContainer;
use Tempest\EventBus\EventBus;
use Tempest\Highlight\Themes\TerminalStyle;
use Tempest\Support\Filesystem;
use Throwable;

final readonly class Debug
{
    private function __construct(
        private ?DebugConfig $config = null,
        private ?EventBus $eventBus = null,
    ) {}

    public static function resolve(): self
    {
        try {
            return new self(
                config: GenericContainer::instance()->get(DebugConfig::class),
                eventBus: GenericContainer::instance()->get(EventBus::class),
            );
        } catch (Throwable) {
            return new self();
        }
    }

    /**
     * Logs and/or dumps the given items.
     *
     * @param bool $writeToLog Whether to write the items to the log file.
     * @param bool $writeToOut Whether to dump the items to the standard output.
     */
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

        $this->eventBus?->dispatch(new ItemsDebugged($items));
    }

    private function writeToLog(array $items, string $callPath): void
    {
        if (! $this->config?->logPath) {
            return;
        }

        Filesystem\create_directory_for_file($this->config->logPath);

        if (! ($handle = @fopen($this->config->logPath, 'a'))) {
            return;
        }

        foreach ($items as $key => $item) {
            fwrite($handle, TerminalStyle::BG_BLUE(" {$key} ") . TerminalStyle::FG_GRAY(' → ' . TerminalStyle::ITALIC($callPath)));
            fwrite($handle, $this->createCliDump($item) . PHP_EOL);
        }

        fclose($handle);
    }

    private function writeToOut(array $items, string $callPath): void
    {
        foreach ($items as $key => $item) {
            if (defined('STDOUT')) {
                fwrite(STDOUT, TerminalStyle::BG_BLUE(" {$key} ") . ' ');
                fwrite(STDOUT, $this->createCliDump($item));
                fwrite(STDOUT, TerminalStyle::DIM('→ ' . TerminalStyle::ITALIC($callPath)) . PHP_EOL . PHP_EOL);
            } else {
                echo
                    vsprintf(
                        <<<HTML
                        <span style="
                            display:inline-block; 
                            color: #fff; 
                            font-family: %s;
                            padding: 2px 4px;
                            font-size: 0.8rem;
                            margin-bottom: -12px;
                            background: #0071BC;"
                        >%s (%s)</span>
                        HTML,
                        [
                            'Source Code Pro, ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, Liberation Mono, Courier New, monospace',
                            $key,
                            $callPath,
                        ],
                    )
                ;

                VarDumper::dump($item);
            }
        }
    }

    private function createCliDump(mixed $input): string
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
