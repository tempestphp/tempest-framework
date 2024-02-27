<?php

declare(strict_types=1);

namespace Tempest\Container;

use LogicException;
use Tempest\Container\Exceptions\CircularDependencyException;

final class ContainerLog
{
    private ContainerLogStep $currentStep;

    public function __construct()
    {
        $this->reset();
    }

    /**
     * @throws CircularDependencyException
     */
    public function add(ContainerLogItem $item): self
    {
        if ($this->currentStep->has($item)) {
            throw new CircularDependencyException($item->id, $this);
        }

        $this->currentStep->add($item);

        return $this;
    }

    public function startStep(): self
    {
        $this->currentStep = new ContainerLogStep($this->currentStep);

        return $this;
    }

    public function completeStep(): mixed
    {
        if ($this->currentStep->hasPreviousStep() === false) {
            throw new LogicException(
                'No step was started. Did you forget to call `startStep()`?'
            );
        }

        $this->currentStep = $this->currentStep->previousStep;

        return $this;
    }

    /**
     * @template TValue
     * @param TValue $value
     * @return TValue
     */
    public function completeStepAfter(mixed $value): mixed
    {
        $this->completeStep();

        return $value;
    }

    public function reset(): self
    {
        $this->currentStep = new ContainerLogStep();

        return $this;
    }

    public function __toString(): string
    {
        $message = '';

        $step = $this->currentStep->killUnfinishedSteps()->getFirstStep();

        while ($step) {
            $break = '';

            if ($step->getStepNumber() > 1) {
                $break = str_repeat("\t", $step->getStepNumber() - 1);
            }

            if ($step->getStepNumber() > 1) {
                $break .= '└── ';
            }

            $message .= $break . $step;

            $step = $step->getNextStep();
        }

        return $message;
    }
}
