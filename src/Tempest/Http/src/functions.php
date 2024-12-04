<?php

declare(strict_types=1);

namespace Tempest {
    use Tempest\Http\Router;
    use Tempest\Reflection\MethodReflector;

    /**
     * Creates a valid URI to the given controller `$action`.
     */
    function uri(array|string|MethodReflector $action, ...$params): string
    {
        if ($action instanceof MethodReflector) {
            $action = [
                $action->getDeclaringClass()->getName(),
                $action->getName(),
            ];
        }

        $router = get(Router::class);

        return $router->toUri(
            $action,
            ...$params,
        );
    }
}
