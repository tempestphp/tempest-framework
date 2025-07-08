<?php

namespace Tempest\View\Components;

use Tempest\Http\Session\Session;

final readonly class InputError
{
    public function __construct(
        private Session $session,
    ) {}

    /** @return \Tempest\Validation\Rule[] */
    public function getErrorsFor(?string $name): ?array
    {
        if ($name === null) {
            return null;
        }

        return $this->session->get(Session::VALIDATION_ERRORS)[$name] ?? null;
    }
}
