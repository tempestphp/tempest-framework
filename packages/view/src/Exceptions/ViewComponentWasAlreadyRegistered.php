<?php

declare(strict_types=1);

namespace Tempest\View\Exceptions;

use Exception;
use Tempest\Reflection\ClassReflector;
use Tempest\View\Components\ViewComponent;

final class ViewComponentWasAlreadyRegistered extends Exception
{
    public function __construct(
        string $name,
        ClassReflector|ViewComponent $pending,
        string|ViewComponent $existing,
    ) {
        $message = sprintf(
            "Could not register view component `{$name}` from `%s`, because a component with the same name already exists in `%s`",
            ($pending instanceof ViewComponent) ? $pending->file : $pending->getName(),
            ($existing instanceof ViewComponent) ? $existing->file : $existing,
        );

        parent::__construct($message);
    }
}
