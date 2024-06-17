<?php

namespace Tempest\View\Elements;

use PHPHtmlParser\Dom\AbstractNode;
use PHPHtmlParser\Dom\InnerNode;
use Tempest\View\Element;
use Tempest\View\View;

final class ElementFactory
{
    public function make(View $view, AbstractNode $node): ?Element
    {
//        $attributes = $this->attributeFactory->makeCollection($view, $node);
//
//        foreach ($attributes as $attribute) {
//            $element = $attribute->apply($element);
//        }

        return $this->makeElement(
            view: $view,
            node: $node,
            parent: null,
        );
    }

    private function makeElement(View $view, AbstractNode $node, ?Element $parent): ?Element
    {
        if ($node->getTag()->name() === 'text') {
            if (trim($node->outerHtml()) === '') {
                return null;
            }

            return new TextElement(
                view: $view,
                text: $node->outerHtml(),
            );
        }

        $element = new GenericElement(
            tag: $node->getTag()->name(),
            attributes: $node->getAttributes(),
        );

        $children = [];

        if ($node instanceof InnerNode) {
            foreach ($node->getChildren() as $child) {
                $childElement = $this->clone()->makeElement(
                    view: $view,
                    node: $child,
                    parent: $parent,
                );

                if ($childElement === null) {
                    continue;
                }

                $children[] = $childElement;
            }
        }

        $element->setChildren($children);

        return $element;
    }

//    private function resolveViewComponent(View $view, AbstractNode $node): ?ViewComponent
//    {
//        /** @var class-string<\Tempest\View\ViewComponent>|null $component */
//        $viewComponentClass = $this->viewConfig->viewComponents[$node->getTag()->name()] ?? null;
//
//        if (! $viewComponentClass) {
//            return null;
//        }
//
//        if ($viewComponentClass instanceof ViewComponent) {
//            return $viewComponentClass;
//        }
//
//        $attributes = [
//            'view' => $view,
//            'slot' => view($node->innerhtml)->data(...$view->getData()),
//        ];
//
//        // TODO: should view components still have attribute injection, or should view components retrieve attribute values via the element?
//        foreach ($node->getAttributes() as $name => $value) {
//            if (str_starts_with($name, ':') && $value) {
//                $value = $view->eval($value);
//                $name = substr($name, 1);
//            }
//
//            $attributes[$name] = $value;
//        }
//
//        $reflection = new ReflectionClass($viewComponentClass);
//
//        $attributesToInject = [];
//
//        foreach ($reflection->getConstructor()->getParameters() as $parameter) {
//            if (array_key_exists($parameter->getName(), $attributes)) {
//                $attributesToInject[$parameter->getName()] = $attributes[$parameter->getName()];
//            } elseif ($parameter->isDefaultValueAvailable()) {
//                $attributesToInject[$parameter->getName()] = $parameter->getDefaultValue();
//            } else {
//                try {
//                    $attributesToInject[$parameter->getName()] = get(type($parameter->getType()));
//                } catch (ReflectionException) {
//                    throw new Exception("Could not resolve value for field {$viewComponentClass}::\${$parameter->name}");
//                }
//            }
//        }
//
//        return $reflection->newInstance(...$attributesToInject);
//    }

    private function clone(): self
    {
        return clone $this;
    }
}