<?php

namespace Tempest\Console\Actions;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Input\ConsoleArgumentBag;

final readonly class CompleteConsoleCommandArguments
{
    use HasConsole;
    
    public function __invoke(
        ConsoleCommand $command,
        ConsoleArgumentBag $argumentBag,
        int $current,
    ): void {
        $definitions = $command->getArgumentDefinitions();

        foreach ($definitions as $definition) {
            if ($definition->type !== 'array' && $argumentBag->has($definition->name)) {
                continue;
            }

            $this->write("--{$definition->name}");

            if ($definition->type !== 'bool') {
                $this->write('=');
            }

            $this->writeln();
        }
    }
}