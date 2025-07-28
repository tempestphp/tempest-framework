<?php

declare(strict_types=1);

namespace Tempest\View\Exceptions;

use Exception;
use Tempest\Reflection\ClassReflector;
use Tempest\View\Components\ViewComponent;

final class ViewComponentWasAlreadyRegistered extends Exception
{
    public function __construct(ViewComponent $pending, ViewComponent $existing)
    {
        $message = sprintf(
            'Could not register view component `%s` from `%s`, because a component with the same name already exists in `%s`',
            $pending->name,
            $pending->file,
            $existing->file,
        );

        parent::__construct($message);
    }
}
