<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use BackedEnum;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\Input\ConsoleArgumentDefinition;

use function Tempest\Support\str;

final readonly class BuildCompletionMetadata
{
    public function __construct(
        private ConsoleConfig $consoleConfig,
    ) {}

    public function __invoke(): array
    {
        $commands = [];

        foreach ($this->consoleConfig->commands as $name => $command) {
            $flags = array_map(
                fn (ConsoleArgumentDefinition $definition): array => [
                    'name' => $definition->name,
                    'flag' => $this->buildFlagNotation($definition),
                    'aliases' => $this->buildFlagAliases($definition),
                    'description' => $definition->description,
                    'value_options' => $this->buildValueOptions($definition),
                    'repeatable' => $definition->type === 'array' || $definition->isVariadic,
                    'requires_value' => $definition->type !== 'bool',
                ],
                $command->getArgumentDefinitions(),
            );

            usort($flags, static fn (array $a, array $b): int => $a['flag'] <=> $b['flag']);

            $commands[$name] = [
                'hidden' => $command->hidden,
                'description' => $command->description,
                'flags' => $flags,
            ];
        }

        ksort($commands);

        return [
            'version' => 1,
            'commands' => $commands,
        ];
    }

    private function buildFlagNotation(ConsoleArgumentDefinition $definition): string
    {
        $flag = "--{$definition->name}";

        if ($definition->type !== 'bool') {
            $flag .= '=';
        }

        return $flag;
    }

    private function buildFlagAliases(ConsoleArgumentDefinition $definition): array
    {
        $aliases = array_values(array_filter(array_map(static function (string $alias): ?string {
            $normalized = ltrim(str($alias)->trim()->kebab()->toString(), '-');

            if ($normalized === '') {
                return null;
            }

            return match (strlen($normalized)) {
                1 => "-{$normalized}",
                default => "--{$normalized}",
            };
        }, $definition->aliases)));

        sort($aliases);

        return $aliases;
    }

    private function buildValueOptions(ConsoleArgumentDefinition $definition): array
    {
        if (! $definition->isBackedEnum()) {
            return [];
        }

        /** @var class-string<BackedEnum> $type */
        $type = $definition->type;

        $options = array_map(
            static fn (BackedEnum $case): string => (string) $case->value,
            $type::cases(),
        );

        sort($options);

        return $options;
    }
}
