<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionParameter;

final readonly class RenderConsoleCommandMessage
{
    public function __invoke(ConsoleCommand $consoleCommand): string
    {
        $description = $consoleCommand->getDescription();

        return ConsoleOutputBuilder::new(' ')
            ->info($consoleCommand->getName())
            ->when(count($consoleCommand->handler->getParameters()) > 0, function (ConsoleOutputBuilder $builder) use ($consoleCommand) {
                foreach ($consoleCommand->handler->getParameters() as $parameter) {
                    $builder->formatted(
                        $this->renderParameter($parameter),
                    );
                }
            })
            ->when($consoleCommand->getDescription() !== '', fn (ConsoleOutputBuilder $builder) => $builder->muted("- " . $description))
            ->toString();
    }

    private function renderParameter(ReflectionParameter $parameter): string
    {
        $type = (string) $parameter->getType();

        $optional = $parameter->isOptional();
        $defaultValue = strtolower(var_export($optional ? $parameter->getDefaultValue() : null, true));
        $name = ConsoleStyle::FG_BLUE($parameter->getName());

        $asString = match($type) {
            'bool' => ConsoleStyle::FG_BLUE("--{$name}"),
            default => $name,
        };

        return match($optional) {
            true => "[{$asString}={$defaultValue}]",
            false => "<{$asString}>",
        };
    }
}
