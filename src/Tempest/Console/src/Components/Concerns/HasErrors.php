<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Concerns;

use Tempest\Console\Components\ComponentState;
use Tempest\Console\InteractiveConsoleComponent;

/**
 * @mixin InteractiveConsoleComponent
 * @phpstan-require-implements InteractiveConsoleComponent
 */
trait HasErrors
{
    /** @var string[] */
    private array $errors = [];

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        // Set the state to ERROR if we have errors and we're not already cancelled.
        if ($this->errors !== [] && $this->getState() === ComponentState::ACTIVE) {
            $this->setState(ComponentState::ERROR);
        }

        return $this;
    }
}
