<?php

declare(strict_types=1);

namespace Tempest {

    use Tempest\Http\Router;
    use Tempest\Support\Reflection\MethodReflector;
    use Tempest\View\GenericView;
    use Tempest\View\View;

    function view(string $path, mixed ...$params): View
    {
        return (new GenericView($path))->data(...$params);
    }

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
