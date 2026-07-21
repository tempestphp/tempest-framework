<?php

declare(strict_types=1);

namespace Tempest\Router\Exceptions;

use Exception;

final class RouteBindingDidNotSupportRelations extends Exception implements RouterException
{
    public function __construct(string $className)
    {
        parent::__construct(sprintf(
            '#[WithRelations] was added to %s, but %s::resolve() does not accept a $relations parameter. Use IsDatabaseModel or update the resolver.',
            $className,
            $className,
        ));
    }
}
