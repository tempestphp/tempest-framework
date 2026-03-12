<?php

declare(strict_types=1);

namespace Tempest\Console\Completion;

final readonly class CompletionEngine
{
    public function __construct(
        private CompletionDescriptionSanitizer $descriptionSanitizer = new CompletionDescriptionSanitizer(),
    ) {}

    public function complete(CompletionMetadata $metadata, CompletionInput $input): array
    {
        if ($input->words === []) {
            return [];
        }

        $current = $input->words[$input->currentIndex] ?? '';

        if ($input->currentIndex <= 1) {
            return $this->completeCommands($metadata, $current);
        }

        $commandName = $input->words[1] ?? '';

        if (str_starts_with($commandName, '-')) {
            return $this->completeCommands($metadata, $current);
        }

        $command = $metadata->findCommand($commandName);

        if (! $command instanceof CompletionCommand) {
            return [];
        }

        return $this->completeFlags($command, $input->words, $input->currentIndex, $current);
    }

    private function completeCommands(CompletionMetadata $metadata, string $current): array
    {
        if (str_starts_with($current, '-')) {
            return [];
        }

        $maxNameLength = 0;
        $candidates = [];

        foreach ($metadata->commands as $name => $command) {
            if (! is_string($name) || ! $command instanceof CompletionCommand) {
                continue;
            }

            if ($command->hidden) {
                continue;
            }

            if (! str_starts_with($name, $current)) {
                continue;
            }

            $maxNameLength = max($maxNameLength, strlen($name));
            $candidates[] = [$name, $this->descriptionSanitizer->sanitize($command->description)];
        }

        $completions = [];

        foreach ($candidates as [$name, $description]) {
            $completions[] = new CompletionCandidate(
                value: $name,
                display: $description === null
                    ? null
                    : sprintf("%-{$maxNameLength}s  %s", $name, $description),
            );
        }

        return $completions;
    }

    private function completeFlags(CompletionCommand $command, array $words, int $currentIndex, string $current): array
    {
        if ($current !== '' && ! str_starts_with($current, '-')) {
            return [];
        }

        $usedFlags = $this->collectUsedFlags($command, $words, $currentIndex);
        $maxLabelLength = 0;
        $candidates = [];

        foreach ($command->flags as $flag) {
            if (! $flag instanceof CompletionFlag) {
                continue;
            }

            if (! $flag->repeatable && isset($usedFlags[$flag->name])) {
                continue;
            }

            $value = $this->selectCompletionValue($flag, $current);

            if ($value === null) {
                continue;
            }

            $label = $this->buildFlagLabel($flag);
            $description = $this->descriptionSanitizer->sanitize($flag->description);

            $maxLabelLength = max($maxLabelLength, strlen($label));
            $candidates[] = [$value, $label, $description];
        }

        $completions = [];

        foreach ($candidates as [$value, $label, $description]) {
            $completions[] = new CompletionCandidate(
                value: $value,
                display: $description === null
                    ? $label
                    : sprintf("%-{$maxLabelLength}s  %s", $label, $description),
            );
        }

        return $completions;
    }

    private function selectCompletionValue(CompletionFlag $flag, string $current): ?string
    {
        $candidates = [];

        if (str_starts_with($current, '--')) {
            $candidates[] = $flag->flag;

            foreach ($flag->aliases as $alias) {
                if (str_starts_with($alias, '--')) {
                    $candidates[] = $alias;
                }
            }
        } elseif (str_starts_with($current, '-')) {
            foreach ($flag->aliases as $alias) {
                $candidates[] = $alias;
            }

            $candidates[] = $flag->flag;
        } else {
            $candidates[] = $flag->flag;
        }

        return array_find($candidates, static fn ($candidate) => str_starts_with($candidate, $current));
    }

    private function buildFlagLabel(CompletionFlag $flag): string
    {
        $label = $flag->flag;

        if ($flag->valueOptions !== [] && str_ends_with($flag->flag, '=')) {
            $label .= '<' . implode(',', $flag->valueOptions) . '>';
        }

        if ($flag->aliases !== []) {
            $label .= ' / ' . implode(' / ', $flag->aliases);
        }

        return $label;
    }

    private function collectUsedFlags(CompletionCommand $command, array $words, int $currentIndex): array
    {
        $used = [];

        for ($index = 2, $max = count($words); $index < $max; $index++) {
            if ($index === $currentIndex) {
                continue;
            }

            $word = $words[$index] ?? null;

            if (! is_string($word)) {
                continue;
            }

            if (str_starts_with($word, '--')) {
                $name = $this->normalizeLongFlag($word);

                if ($name === null) {
                    continue;
                }

                $flagName = $command->resolveFlagName($name);

                if ($flagName !== null) {
                    $used[$flagName] = true;
                }

                continue;
            }

            if (! str_starts_with($word, '-')) {
                continue;
            }

            $shortValue = explode('=', $word, 2)[0];
            $shortValue = ltrim($shortValue, '-');

            if (strlen($shortValue) === 1) {
                $flagName = $command->resolveFlagName($shortValue);

                if ($flagName !== null) {
                    $used[$flagName] = true;
                }

                continue;
            }

            foreach (str_split($shortValue) as $part) {
                $flagName = $command->resolveFlagName($part);

                if ($flagName !== null) {
                    $used[$flagName] = true;
                }
            }
        }

        return $used;
    }

    private function normalizeLongFlag(string $value): ?string
    {
        $normalized = $value;

        while (str_starts_with($normalized, '--')) {
            $normalized = substr($normalized, 2);
        }

        $normalized = explode('=', $normalized, 2)[0];

        if (str_starts_with($normalized, 'no-')) {
            $normalized = substr($normalized, 3);
        }

        if ($normalized === '') {
            return null;
        }

        return $normalized;
    }
}
