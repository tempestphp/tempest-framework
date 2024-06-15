<?php

namespace Tempest\View\Elements;

use Exception;
use PHPHtmlParser\Dom\AbstractNode;
use PHPHtmlParser\Dom\InnerNode;
use ReflectionClass;
use ReflectionException;
use Tempest\View\Element;
use Tempest\View\View;
use Tempest\View\ViewComponent;
use Tempest\View\ViewConfig;
use function Tempest\get;
use function Tempest\type;
use function Tempest\view;

final readonly class ElementFactory
{
    public function __construct(
        private ViewConfig $viewConfig,
    ) {}

    public function make(View $view, AbstractNode $node): Element
    {
        if ($node->getTag()->name() === 'text') {
            return new TextElement($view, $node->text());
        }

        if ($viewComponent = $this->resolveViewComponent($view, $node)) {
            return $viewComponent;
        }

        $children = [];

        if ($node instanceof InnerNode) {
            foreach ($node->getChildren() as $child) {
                $children[] = $this->make($view, $child);
            }
        }

        return new GenericElement(
            html: $node->outerHtml(),
            tag: $node->getTag()->name(),
            attributes: $node->getAttributes(),
            children: $children,
        );
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

        foreach ($node->getAttributes() as $name => $value) {
            if (str_starts_with($name, ':')) {
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
}