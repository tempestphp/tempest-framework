<?php

namespace Tempest\View\Elements;

use Exception;
use PHPHtmlParser\Dom\AbstractNode;
use PHPHtmlParser\Dom\InnerNode;
use ReflectionClass;
use ReflectionException;
use Tempest\View\Attributes\AttributeFactory;
use Tempest\View\Element;
use Tempest\View\View;
use Tempest\View\ViewComponent;
use Tempest\View\ViewConfig;
use function Tempest\get;
use function Tempest\type;
use function Tempest\view;

final class ElementFactory
{
    private ?Element $previous = null;

    public function __construct(
        private readonly ViewConfig $viewConfig,
        private readonly AttributeFactory $attributeFactory,
    ) {}

    public function make(View $view, AbstractNode $node): Element
    {
        $element = $this->makeElement($view, $node);

        $attributes = $this->attributeFactory->makeCollection($view, $node);

        foreach ($attributes as $attribute) {
            $element = $attribute->apply($element);
        }

        $this->previous = $element;

        return $element;
    }

    private function makeElement(View $view, AbstractNode $node): Element
    {
        if ($node->getTag()->name() === 'text') {
            return new TextElement(
                view: $view,
                text: $node->outerHtml(),
                previous: $this->previous,
                attributes: $node->getAttributes(),
            );
        }

        if ($viewComponent = $this->resolveViewComponent($view, $node)) {
            return new ViewComponentElement(
                viewComponent: $viewComponent,
                previous: $this->previous,
                attributes: $node->getAttributes(),
            );
        }

        $element = new GenericElement(
            html: $node->outerHtml(),
            tag: $node->getTag()->name(),
            previous: $this->previous,
            attributes: $node->getAttributes(),
        );

        $children = [];

        if ($node instanceof InnerNode) {
            foreach ($node->getChildren() as $child) {
                $children[] = $this->clone()->make(
                    view: $view,
                    node: $child,
                );
            }
        }


        $element->setChildren($children);

        return $element;
    }

    private function resolveViewComponent(View $view, AbstractNode $node): ?ViewComponent
    {
        /** @var class-string<\Tempest\View\ViewComponent>|null $component */
        $viewComponentClass = $this->viewConfig->viewComponents[$node->getTag()->name()] ?? null;

        if (! $viewComponentClass) {
            return null;
        }

        if ($viewComponentClass instanceof ViewComponent) {
            return $viewComponentClass;
        }

        $attributes = [
            'view' => $view,
            'slot' => view($node->innerhtml)->data(...$view->getData()),
        ];

        // TODO: should view components still have attribute injection, or should view components retrieve attribute values via the element?
        foreach ($node->getAttributes() as $name => $value) {
            if (str_starts_with($name, ':') && $value) {
                $value = $view->eval($value);
                $name = substr($name, 1);
            }

            $attributes[$name] = $value;
        }

        $reflection = new ReflectionClass($viewComponentClass);

        $attributesToInject = [];

        foreach ($reflection->getConstructor()->getParameters() as $parameter) {
            if (array_key_exists($parameter->getName(), $attributes)) {
                $attributesToInject[$parameter->getName()] = $attributes[$parameter->getName()];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $attributesToInject[$parameter->getName()] = $parameter->getDefaultValue();
            } else {
                try {
                    $attributesToInject[$parameter->getName()] = get(type($parameter->getType()));
                } catch (ReflectionException) {
                    throw new Exception("Could not resolve value for field {$viewComponentClass}::\${$parameter->name}");
                }
            }
        }

        return $reflection->newInstance(...$attributesToInject);
    }

    private function clone(): self
    {
        return clone $this;
    }
}