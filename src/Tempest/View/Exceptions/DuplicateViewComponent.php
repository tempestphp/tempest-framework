<?php

declare(strict_types=1);

namespace Tempest\View\Exceptions;

use Exception;
use Tempest\Support\Reflection\ClassReflector;
use Tempest\View\Components\AnonymousViewComponent;

final class DuplicateViewComponent extends Exception
{
    public function __construct(
        string $name,
        ClassReflector|AnonymousViewComponent $pending,
        string|AnonymousViewComponent $existing,
    ) {
        $message = sprintf(
            "Could not register view component `{$name}` from `%s`, because a component with the same name already exists in `%s`",
            $pending instanceof AnonymousViewComponent ? $pending->getPath() : $pending->getName(),
            $existing instanceof AnonymousViewComponent ? $existing->getPath() : $existing,
        );

        parent::__construct($message);
    }
}
