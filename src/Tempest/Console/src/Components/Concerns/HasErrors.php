<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Concerns;

use Tempest\Console\Components\State;
use Tempest\Console\InteractiveConsoleComponent;

/** @mixin InteractiveConsoleComponent */
trait HasErrors
{
    /** @var string[] */
    private array $errors = [];

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        // Set the state to ERROR if we have errors and we're not already cancelled.
        if ($this->errors !== [] && $this->getState() === State::ACTIVE) {
            $this->setState(State::ERROR);
        }

        return $this;
    }
}