<?php

declare(strict_types=1);

namespace Tempest\Validation;

use Tempest\Validation\Exceptions\ValidationException;

interface Validator
{

    /**
     * @param mixed $value
     *
     * @throws ValidationException
     *
     * @return void
     */
    public function validate(mixed $value): void;

}
