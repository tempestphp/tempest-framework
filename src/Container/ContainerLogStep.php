<?php

declare(strict_types=1);

namespace Tempest\Container;

final class ContainerLogStep
{
    private array $lines = [];
    private int $stepNumber;
    private ?ContainerLogStep $nextStep = null;

    public function __construct(
        public readonly ?ContainerLogStep $previousStep = null
    ) {
        $this->stepNumber = ($this->previousStep !== null)
            ? $this->previousStep->getStepNumber() + 1
            : 0;

        $this->previousStep?->setNextStep($this);
    }

    public function killUnfinishedSteps(): self
    {
        $step = $this;

        if (! $this->hasLines()) {
            $step = $this->previousStep;
            $step->nextStep = null;
        }

        return $step;
    }

    public function add(ContainerLogItem $item): self
    {
        $this->lines[$item->id] = $item;

        return $this;
    }

    public function has(ContainerLogItem $id): bool
    {
        return in_array($id, $this->lines) || in_array($id, $this->previousStep?->getLines() ?? []);
    }

    public function hasLines(): bool
    {
        return count($this->lines) !== 0;
    }

    public function hasPreviousStep(): bool
    {
        return $this->previousStep !== null;
    }

    public function hasNextStep(): bool
    {
        return $this->nextStep !== null;
    }

    public function getLines(): array
    {
        if ($this->previousStep === null) {
            return $this->lines;
        }

        return [
            ...$this->previousStep?->getLines(),
            ...$this->lines,
        ];
    }

    public function getFirstStep(): self
    {
        $step = $this;

        while ($step->hasPreviousStep()) {
            $step = $step->previousStep;
        }

        return $step;
    }

    public function getNextStep(): ?self
    {
        return $this->nextStep;
    }

    public function getStepNumber(): int
    {
        return $this->stepNumber;
    }

    public function __toString(): string
    {
        $compiled = '';

        foreach ($this->lines as $line) {
            $compiled .= $line;
        }

        return $compiled . PHP_EOL;
    }

    private function setNextStep(self $nextStep): void
    {
        $this->nextStep = $nextStep;
    }
}
