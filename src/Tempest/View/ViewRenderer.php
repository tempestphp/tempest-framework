<?php

declare(strict_types=1);

namespace Tempest\View;

use Exception;
use PHPHtmlParser\Dom;
use Tempest\Application\AppConfig;
use Tempest\View\Attributes\AttributeFactory;
use Tempest\View\Elements\ElementFactory;
use Tempest\View\Elements\GenericElement;
use function Tempest\path;

final readonly class ViewRenderer
{
    public function __construct(
        private ElementFactory $elementFactory,
        private AttributeFactory $attributeFactory,
        private AppConfig $appConfig,
        private ViewConfig $viewConfig,
    ) {}

    public function render(?View $view): string
    {
        if ($view === null) {
            return '';
        }

        $contents = $this->resolveContent($view);

        $dom = new Dom();

        $dom->load('<div>' . $contents . '</div>');

        $element = $this->applyAttributes(
            view: $view,
            element: $this->elementFactory->make($view,
                $dom->root->getChildren()[0],
            ),
        );

        return trim($this->renderElements($view, $element->getChildren()));
    }

    /** @param \Tempest\View\Element[] $elements */
    private function renderElements(View $view, array $elements): string
    {
        $rendered = [];

        foreach ($elements as $element) {
            $rendered[] = $this->renderElement($view, $element);
        }

        return implode('', $rendered);
    }

    private function renderElement(View $view, Element $element): string
    {
        $element->addData(...$view->getData());

        if (! $element instanceof GenericElement) {
            return $element->render($this);
        }

        $viewComponent = $this->resolveViewComponent($element);

        if (! $viewComponent) {
            return $element->render($this);
        }

        return $this->renderSlots(
            rendered: $viewComponent->render($element, $this),
            element: $element,
        );
    }

    private function resolveContent(View $view): string
    {
        $path = $view->getPath();

        if (! str_ends_with($path, '.php')) {
            ob_start();

            /** @phpstan-ignore-next-line */
            eval('?>' . $path . '<?php');

            return ob_get_clean();
        }

        $discoveryLocations = $this->appConfig->discoveryLocations;

        while (! file_exists($path) && $location = current($discoveryLocations)) {
            $path = path($location->path, $view->getPath());
            next($discoveryLocations);
        }

        if (! file_exists($path)) {
            throw new Exception("View {$path} not found");
        }

        ob_start();

        include $path;

        return ob_get_clean();
    }

    private function resolveViewComponent(GenericElement $element): ?ViewComponent
    {
        /** @var class-string<\Tempest\View\ViewComponent>|null $component */
        $viewComponentClass = $this->viewConfig->viewComponents[$element->getTag()] ?? null;

        if (! $viewComponentClass) {
            return null;
        }

        if ($viewComponentClass instanceof ViewComponent) {
            return $viewComponentClass;
        } else {
            return new $viewComponentClass;
        }
//
//        $attributes = [
//            'view' => $view,
//            'slot' => '', // TODO rendered child contents
//        ];

        // TODO: should view components still have attribute injection, or should view components retrieve attribute values via the element?
//        foreach ($node->getAttributes() as $name => $value) {
//            if (str_starts_with($name, ':') && $value) {
//                $value = $view->eval($value);
//                $name = substr($name, 1);
//            }
//
//            $attributes[$name] = $value;
//        }

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
    }

    private function applyAttributes(View $view, Element $element): Element
    {
        if (! $element instanceof GenericElement) {
            return $element;
        }

        $children = [];

        foreach ($element->getChildren() as $child) {
            $children[] = $this->applyAttributes($view, $child);
        }

        $element->setChildren($children);

        foreach ($element->getAttributes() as $name => $value) {
            $attribute = $this->attributeFactory->make($view, $name, $value);

            $element = $attribute->apply($element);
        }

        return $element;
    }

    private function renderSlots(string $rendered, Element $element): string
    {
        if (! $element instanceof GenericElement) {
            return $rendered;
        }

        return preg_replace_callback(
            pattern: '/<x-slot\s*(name="(?<name>\w+)")?\s*\/>/',
            callback: function ($matches) use ($element) {
                $name = $matches['name'] ?? 'slot';

                return $element->getSlot($name)?->render($this) ?? $matches[0];
            },
            subject: $rendered,
        );
    }

}
