<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\DateTime\DateTime;
use Tempest\DateTime\Duration;
use Tempest\Reflection\MethodReflector;
use Tempest\Router\UriGenerator;

use function Tempest\Container\get;

/**
 * Creates a valid URI to the given `$action`.
 *
 * `$action` is one of :
 * - Controller FQCN and its method as a tuple
 * - Invokable controller FQCN
 * - URI string starting with `/`
 *
 * @param MethodReflector|array{class-string,string}|class-string|string $action
 */
function uri(array|string|MethodReflector $action, mixed ...$params): string
{
    return get(UriGenerator::class)->createUri($action, ...$params);
}

/**
 * Creates a URI that is signed with a secret key, ensuring that it cannot be tampered with.
 *
 * `$action` is one of :
 * - Controller FQCN and its method as a tuple
 * - Invokable controller FQCN
 * - URI string starting with `/`
 *
 * @param MethodReflector|array{class-string,string}|class-string|string $action
 */
function signed_uri(array|string|MethodReflector $action, mixed ...$params): string
{
    return get(UriGenerator::class)->createSignedUri($action, ...$params);
}

/**
 * Creates an absolute URI that is signed with a secret key and will expire after the specified duration.
 *
 * `$action` is one of :
 * - Controller FQCN and its method as a tuple
 * - Invokable controller FQCN
 * - URI string starting with `/`
 *
 * @param MethodReflector|array{class-string,string}|class-string|string $action
 */
function temporary_signed_uri(array|string|MethodReflector $action, DateTime|Duration|int $duration, mixed ...$params): string
{
    return get(UriGenerator::class)->createTemporarySignedUri($action, $duration, ...$params);
}

/**
 * Checks if the URI to the given `$action` would match the current route.
 *
 * `$action` is one of :
 * - Controller FQCN and its method as a tuple
 * - Invokable controller FQCN
 * - URI string starting with `/`
 *
 * @param MethodReflector|array{class-string,string}|class-string|string $action
 */
function is_current_uri(array|string|MethodReflector $action, mixed ...$params): bool
{
    return get(UriGenerator::class)->isCurrentUri($action, ...$params);
}
