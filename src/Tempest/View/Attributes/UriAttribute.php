<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Http\Get;
use Tempest\Http\Post;
use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\GenericElement;
use Tempest\View\Elements\TextElement;

final class UriAttribute implements Attribute
{
    private Container $container;

    public function __construct()
    {
        $this->container = GenericContainer::instance();
    }

    public function apply(Element $element): Element
    {
        if (! $element instanceof GenericElement) {
            return $element;
        }

        /** @var Router $router */
        $router = $this->container->get(Router::class);

        // resolve the route from the container
        $controller = $element->getAttribute('uri', eval: false);

        return new TextElement(sprintf('<a href="%s">%s</a>', $uri, $body->getText()));
    }

    private function parseAction(GenericElement $element): array
    {
        $attributes = current($element->getAttributes());
        if (! str_contains($attributes, '[')) {
            return ['controller' => str_replace('::class', '', $attributes)];
        }

        $data = explode(', ', str_replace(['[', ']'], '', $attributes));
        $controller = str_replace('::class', '', array_shift($data));
        $method = array_shift($data);

        return compact('controller', 'method', 'data');
    }

    private function getHttpAttribute(ReflectionMethod $method): mixed
    {
        if ($get = current($method->getAttributes(Get::class))) {
            return new Get($get->getArguments()['uri']);
        }

        return current($method->getAttributes(Post::class));
    }

    private function hasHttpAttribute(ReflectionMethod $invokeMethod): bool
    {
        $attributes = array_filter($invokeMethod->getAttributes(), static fn (ReflectionAttribute $attribute): bool => match ($attribute->getName()) {
            Get::class,
            Post::class => true,
            default => false,
        });

        return count($attributes) === 1;
    }

    /** @throws ReflectionException */
    private function getMethod(GenericElement $element): ?ReflectionMethod
    {
        $action = $this->parseAction($element);
        $resolved = $this->container->get($action['controller']);

        $controller = (new ReflectionClass($resolved));

        return array_filter($controller->getMethods(), static function (ReflectionMethod $method) use ($action) {
            if (is_null($action['method'])) {
                return false;
            }

            return $method->getName() === str_replace("'", '', $action['method']);
        })[0] ?? null;
    }
}
