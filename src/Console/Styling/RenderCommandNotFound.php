<?php

declare(strict_types=1);

namespace Tempest\Console\Styling;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\RenderConsoleCommand;

final readonly class RenderCommandNotFound
{
    public function __construct(
        protected ConsoleConfig $consoleConfig,
        protected RenderConsoleCommand $renderConsoleCommand,
    ) {

    }

    public function __invoke(string $command): string
    {
        $similarCommands = $this->getSimilarCommands($command);

        return ConsoleOutputBuilder::new()
            ->error("Command `{$command}` not found")
            ->blank()
            ->when(! ! $similarCommands, function (ConsoleOutputBuilder $response) use ($similarCommands) {
                $response->warning('Did you mean one of these commands?')
                    ->blank();

                foreach ($similarCommands as $similarCommand) {
                    $response->formatted("  - " . ($this->renderConsoleCommand->__invoke($similarCommand)));
                }
            })
            ->toString();
    }

    /**
     * @return ConsoleCommand[]
     */
    protected function getSimilarCommands(string $command): array
    {
        $similarCommands = [];

        foreach ($this->consoleConfig->commands as $consoleCommand) {
            $levenshtein = levenshtein($command, $consoleCommand->getName());

            if ($levenshtein <= 3) {
                $similarCommands[] = $consoleCommand;
            }
        }

        return $similarCommands;
    }
}
