<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Concerns;

use Tempest\Console\Components\State;
use Tempest\Console\HandlesKey;
use Tempest\Console\Key;

trait HasState
{
    private State $state = State::ACTIVE;

    public function getState(): State
    {
        return $this->state;
    }

    public function setState(State $state): void
    {
        $this->state = $state;
    }

    #[HandlesKey(Key::ENTER)]
    public function setSubmitted(): void
    {
        $this->state = State::SUBMITTED;
    }
}
