<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Concerns;

use Tempest\Console\CanCancel;
use Tempest\Console\Components\State;
use Tempest\Console\Exceptions\InterruptException;
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

    #[HandlesKey(Key::CTRL_C)]
    public function setCancelled(): void
    {
        if (! is_subclass_of(static::class, CanCancel::class)) {
            return;
        }

        $this->state = State::CANCELLED;

        throw new InterruptException();
    }
}
