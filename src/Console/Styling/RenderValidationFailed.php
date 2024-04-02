<?php

declare(strict_types=1);

namespace Tempest\Console\Styling;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleStyle;
use Tempest\Console\RenderConsoleCommand;
use Tempest\Validation\Exceptions\ValidationException;

final readonly class RenderValidationFailed
{
    public function __construct(
        protected ConsoleConfig $consoleConfig,
        protected RenderConsoleCommand $renderConsoleCommand,
    ) {

    }

    public function __invoke(ConsoleCommand $command, ValidationException $exception): string
    {
        return ConsoleOutputBuilder::new()
            ->error(' Validation failed ')
            ->blank()
            ->formatted(
                $this->renderConsoleCommand->__invoke($command, true, errorParts: $exception->failingRules),
            )
            ->blank()
            ->error(sprintf(" Found %s errors ", count($exception->failingRules)))
            ->blank()
            ->when(! ! $exception->failingRules, function (ConsoleOutputBuilder $b) use ($exception) {
                foreach ($exception->failingRules as $property => $rules) {
                    $b->formatted(ConsoleStyle::FG_BLUE($property) . ':');

                    foreach ($rules as $rule) {
                        $b->formatted(' - ' . $rule->message());
                    }
                }
            })
            ->toString();
    }
}
