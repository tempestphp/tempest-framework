<?php

declare(strict_types=1);

namespace Tempest\Console\Completion;

use Tempest\Support\Json\Exception\JsonCouldNotBeDecoded;

use function Tempest\Support\Json\decode;

final readonly class CompletionMetadataParser
{
    public function parseJson(string $json): ?CompletionMetadata
    {
        try {
            $metadata = decode($json, associative: true);
        } catch (JsonCouldNotBeDecoded) {
            return null;
        }

        return $this->parse($metadata);
    }

    public function parse(mixed $metadata): ?CompletionMetadata
    {
        if (! is_array($metadata)) {
            return null;
        }

        $commandsMetadata = $metadata['commands'] ?? [];

        if (! is_array($commandsMetadata)) {
            return null;
        }

        $commands = [];

        foreach ($commandsMetadata as $name => $commandMetadata) {
            if (! is_string($name) || ! is_array($commandMetadata)) {
                return null;
            }

            $command = $this->parseCommand($commandMetadata);

            if (! $command instanceof CompletionCommand) {
                return null;
            }

            $commands[$name] = $command;
        }

        ksort($commands);

        return new CompletionMetadata($commands);
    }

    private function parseCommand(array $command): ?CompletionCommand
    {
        $hidden = $command['hidden'] ?? false;

        if (! is_bool($hidden)) {
            return null;
        }

        $description = $command['description'] ?? null;

        if (! is_string($description) && $description !== null) {
            return null;
        }

        $flags = $this->parseFlags($command['flags'] ?? []);

        if (! is_array($flags)) {
            return null;
        }

        return new CompletionCommand(
            hidden: $hidden,
            description: $description,
            flags: $flags,
            flagNameLookup: $this->buildFlagNameLookup($flags),
        );
    }

    private function parseFlags(mixed $flagsMetadata): ?array
    {
        if (! is_array($flagsMetadata)) {
            return null;
        }

        $flags = [];

        foreach ($flagsMetadata as $flagMetadata) {
            if (! is_array($flagMetadata)) {
                return null;
            }

            $flag = $this->parseFlag($flagMetadata);

            if (! $flag instanceof CompletionFlag) {
                return null;
            }

            $flags[] = $flag;
        }

        return $flags;
    }

    private function parseFlag(array $flag): ?CompletionFlag
    {
        $name = $flag['name'] ?? null;
        $notation = $flag['flag'] ?? null;
        $repeatable = $flag['repeatable'] ?? null;

        if (! is_string($name) || ! is_string($notation) || ! is_bool($repeatable)) {
            return null;
        }

        $aliases = $this->parseStringList($flag['aliases'] ?? []);

        if (! is_array($aliases)) {
            return null;
        }

        $description = $flag['description'] ?? null;

        if (! is_string($description) && $description !== null) {
            return null;
        }

        $valueOptions = $this->parseStringList($flag['value_options'] ?? []);

        if (! is_array($valueOptions)) {
            return null;
        }

        return new CompletionFlag(
            name: $name,
            flag: $notation,
            aliases: $aliases,
            description: $description,
            valueOptions: $valueOptions,
            repeatable: $repeatable,
        );
    }

    private function parseStringList(mixed $values): ?array
    {
        if (! is_array($values)) {
            return null;
        }

        $strings = [];

        foreach ($values as $value) {
            if (! is_string($value)) {
                return null;
            }

            $strings[] = $value;
        }

        return $strings;
    }

    private function buildFlagNameLookup(array $flags): array
    {
        $lookup = [];

        foreach ($flags as $flag) {
            $lookup[$flag->name] = $flag->name;

            $normalizedFlag = $this->normalizeFlagLookupValue($flag->flag);

            if ($normalizedFlag !== null) {
                $lookup[$normalizedFlag] = $flag->name;
            }

            foreach ($flag->aliases as $alias) {
                $normalizedAlias = $this->normalizeFlagLookupValue($alias);

                if ($normalizedAlias !== null) {
                    $lookup[$normalizedAlias] = $flag->name;
                }
            }
        }

        return $lookup;
    }

    private function normalizeFlagLookupValue(string $value): ?string
    {
        $normalizedValue = ltrim($value, '-');
        $normalizedValue = rtrim($normalizedValue, '=');

        if (str_starts_with($normalizedValue, 'no-')) {
            $normalizedValue = substr($normalizedValue, 3);
        }

        if ($normalizedValue === '') {
            return null;
        }

        return $normalizedValue;
    }
}
