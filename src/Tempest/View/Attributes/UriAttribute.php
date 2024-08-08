<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Http\Router;
use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\GenericElement;
use Tempest\View\Elements\TextElement;
use Tempest\View\GenericView;

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

        /** @var TextElement $child */
        $child = $element->getChildren()[0];
        $body = $child->getText();

        return new TextElement($body);
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
}
