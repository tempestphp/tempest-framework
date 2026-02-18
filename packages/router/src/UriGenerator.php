<?php

namespace Tempest\Router;

use BackedEnum;
use RuntimeException;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Cryptography\Signing\Signature;
use Tempest\Cryptography\Signing\Signer;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\Duration;
use Tempest\Http\Request;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\MethodReflector;
use Tempest\Router\Exceptions\ControllerActionDoesNotExist;
use Tempest\Router\Exceptions\ControllerMethodHadNoRoute;
use Tempest\Router\Routing\Construction\DiscoveredRoute;
use Tempest\Support\Arr;
use Tempest\Support\Regex;
use Tempest\Support\Str;

use function Tempest\Support\str;

final class UriGenerator
{
    public function __construct(
        private AppConfig $appConfig,
        private RouteConfig $routeConfig,
        private Signer $signer,
        private Container $container,
    ) {}

    /**
     * Checks if the request has a valid signature.
     */
    public function hasValidSignature(Request $request): bool
    {
        $signature = $request->get('signature');
        $expiresAt = $request->get('expires_at');

        if ($signature === null) {
            return false;
        }

        if ($expiresAt !== null && is_numeric($expiresAt) && DateTime::fromTimestamp((int) $expiresAt)->isPast()) {
            return false;
        }

        return $this->signer->verify(
            data: $this->createUri($request->path, ...Arr\remove_keys($request->query, 'signature')),
            signature: Signature::from($signature),
        );
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
    public function createTemporarySignedUri(array|string|MethodReflector $action, DateTime|Duration|int $duration, mixed ...$params): string
    {
        $uri = $this->normalizeActionToUri($action);

        if (array_key_exists('expires_at', $params)) {
            throw new RuntimeException('Cannot create a signed URI with an "expires_at" parameter. It will be added automatically.');
        }

        if (is_int($duration)) {
            $duration = Duration::seconds($duration);
        }

        if ($duration instanceof Duration) {
            $duration = DateTime::now()->plusMilliseconds((int) $duration->getTotalMilliseconds());
        }

        return $this->createSignedUri($uri, ...[
            ...$params,
            'expires_at' => $duration->getTimestamp()->getSeconds(),
        ]);
    }

    /**
     * Creates an absolute URI that is signed with a secret key, ensuring that it cannot be tampered with.
     *
     * `$action` is one of :
     * - Controller FQCN and its method as a tuple
     * - Invokable controller FQCN
     * - URI string starting with `/`
     *
     * @param MethodReflector|array{class-string,string}|class-string|string $action
     */
    public function createSignedUri(array|string|MethodReflector $action, mixed ...$params): string
    {
        $uri = $this->normalizeActionToUri($action);

        if (array_key_exists('signature', $params)) {
            throw new RuntimeException('Cannot create a signed URI with a "signature" parameter. It will be added automatically.');
        }

        $this->signer = $this->container->get(Signer::class);

        ksort($params);

        return $this->createUri($uri, ...[
            ...$params,
            'signature' => $this->signer->sign($this->createUri($action, ...$params))->value,
        ]);
    }

    /**
     * Creates an absolute URI to the given `$action`.
     *
     * `$action` is one of :
     * - Controller FQCN and its method as a tuple
     * - Invokable controller FQCN
     * - URI string starting with `/`
     *
     * @param MethodReflector|array{class-string,string}|class-string|string $action
     */
    public function createUri(array|string|MethodReflector $action, mixed ...$params): string
    {
        $uri = str($this->normalizeActionToUri($action));
        $queryParams = [];

        foreach ($params as $key => $value) {
            if (! $uri->matches(sprintf('/\{%s(\}|:)/', $key))) {
                $queryParams[$key] = $value;

                continue;
            }

            if ($value instanceof BackedEnum) {
                $value = $value->value;
            } elseif ($value instanceof Bindable) {
                foreach (new ClassReflector($value)->getPublicProperties() as $property) {
                    if (! $property->hasAttribute(IsBindingValue::class)) {
                        continue;
                    }

                    $value = $property->getValue($value);

                    break;
                }
            }

            $uri = $uri->replaceRegex(
                regex: '#\{' . $key . DiscoveredRoute::ROUTE_PARAM_CUSTOM_REGEX . '\}#',
                replace: (string) $value,
            );
        }

        $uri = $uri->prepend(rtrim($this->appConfig->baseUri, '/'));

        if ($queryParams !== []) {
            return $uri->append('?' . http_build_query($queryParams))->toString();
        }

        return $uri->toString();
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
    public function isCurrentUri(array|string|MethodReflector $action, mixed ...$params): bool
    {
        $action = $this->normalizeActionToUri($action);

        if (! $this->container->has(MatchedRoute::class)) {
            return false;
        }

        $matchedRoute = $this->container->get(MatchedRoute::class);
        $candidateUri = $this->createUri($action, ...[...$matchedRoute->params, ...$params]);
        $currentUri = $this->createUri([$matchedRoute->route->handler->getDeclaringClass()->getName(), $matchedRoute->route->handler->getName()]);

        foreach ($matchedRoute->params as $key => $value) {
            if ($value instanceof BackedEnum) {
                $value = $value->value;
            }

            $currentUri = Regex\replace($currentUri, '/({' . preg_quote($key, '/') . '(?::.*?)?})/', $value);
        }

        return $currentUri === $candidateUri;
    }

    private function normalizeActionToUri(array|string|MethodReflector $action): string
    {
        if ($action instanceof MethodReflector) {
            $action = [
                $action->getDeclaringClass()->getName(),
                $action->getName(),
            ];
        }

        if (is_string($action) && str_starts_with($action, '/')) {
            return $action;
        }

        [$controllerClass, $controllerMethod] = is_array($action) ? $action : [$action, '__invoke'];

        $routes = array_unique($this->routeConfig->handlerIndex[$controllerClass . '::' . $controllerMethod] ?? []);

        if ($routes === []) {
            if (! class_exists($controllerClass)) {
                throw ControllerActionDoesNotExist::controllerNotFound($controllerClass);
            }

            if (! method_exists($controllerClass, $controllerMethod)) {
                throw ControllerActionDoesNotExist::actionNotFound($controllerClass, $controllerMethod);
            }

            throw new ControllerMethodHadNoRoute($controllerClass, $controllerMethod);
        }

        return Str\ensure_starts_with($routes[0], '/');
    }
}
