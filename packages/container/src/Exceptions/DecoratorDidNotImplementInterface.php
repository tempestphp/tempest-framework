<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;

final class DecoratorDidNotImplementInterface extends Exception implements ContainerException
{
    public function __construct(
        string $className,
        string $decoratorName,
    ) {
        $message = "Cannot resolve {$className} because it is decorated by decorator {$decoratorName}, which does not implement {$className}." . PHP_EOL;
        parent::__construct($message);
    }
}
