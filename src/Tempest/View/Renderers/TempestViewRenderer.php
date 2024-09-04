<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

use Exception;
use Masterminds\HTML5;
use ParseError;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use function Tempest\path;
use Tempest\View\Attributes\AttributeFactory;
use Tempest\View\Element;
use Tempest\View\Elements\CollectionElement;
use Tempest\View\Elements\ElementFactory;
use Tempest\View\Elements\EmptyElement;
use Tempest\View\Elements\GenericElement;
use Tempest\View\Elements\RawElement;
use Tempest\View\Elements\SlotElement;
use Tempest\View\Elements\TextElement;
use Tempest\View\GenericView;
use Tempest\View\View;
use Tempest\View\ViewComponent;
use Tempest\View\ViewComponentView;
use Tempest\View\ViewConfig;
use Tempest\View\ViewRenderer;

final class TempestViewRenderer implements ViewRenderer
{
    private ?View $currentView = null;

    public function __construct(
        private readonly ElementFactory $elementFactory,
        private readonly AttributeFactory $attributeFactory,
        private readonly AppConfig $appConfig,
        private readonly ViewConfig $viewConfig,
        private readonly Container $container,
    ) {
    }

    public function __get(string $name): mixed
    {
        return $this->currentView?->get($name);
    }

    public function __call(string $name, array $arguments)
    {
        return $this->currentView?->{$name}(...$arguments);
    }

    public function render(string|View|null $view): string
    {
        if ($view === null) {
            return '';
        }

        if (is_string($view)) {
            $view = new GenericView($view);
        }

        $this->currentView = $view;

        $contents = $this->resolveContent($view);

        $html5 = new HTML5();
        $dom = $html5->loadHTML("<div id='tempest_render'>{$contents}</div>");

        $element = $this->elementFactory->make(
            $view,
            $dom->getElementById('tempest_render'),
        );

        $element = $this->applyAttributes(
            view: $view,
            element: $element,
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

        return implode(PHP_EOL, $rendered);
    }

    public function renderElement(View $view, Element $element): string
    {
        if ($element instanceof CollectionElement) {
            return $this->renderCollectionElement($view, $element);
        }

        if ($element instanceof TextElement) {
            return $this->renderTextElement($view, $element);
        }

        if ($element instanceof EmptyElement) {
            return $this->renderEmptyElement();
        }

        if ($element instanceof SlotElement) {
            return $this->renderSlotElement($view, $element);
        }

        if ($element instanceof RawElement) {
            return $this->renderRawElement($element);
        }

        if ($element instanceof GenericElement) {
            $viewComponent = $this->resolveViewComponent($element);

            if ($viewComponent === null) {
                return $this->renderGenericElement($view, $element);
            }

            return $this->renderViewComponent(
                view: $view,
                viewComponent: $viewComponent,
                element: $element,
            );
        }

        throw new Exception("No rendered found");
    }

    private function resolveContent(View $view): string
    {
        $path = $view->getPath();

        if (! str_ends_with($path, '.php')) {
            ob_start();

            try {
                // TODO: find a better way of dealing with views that declare strict types
                $path = str_replace('declare(strict_types=1);', '', $path);

                /** @phpstan-ignore-next-line */
                eval('?>' . $path . '<?php');
            } catch (ParseError) {
                return $path;
            }

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

        $data = $view->getData();

        extract($data, flags: EXTR_SKIP);

        include $path;

        return ob_get_clean();
    }

    private function resolveViewComponent(GenericElement $element): ?ViewComponent
    {
        /** @var class-string<\Tempest\View\ViewComponent>|\Tempest\View\ViewComponent|null $viewComponentClass */
        $viewComponentClass = $this->viewConfig->viewComponents[$element->getTag()] ?? null;

        if (! $viewComponentClass) {
            return null;
        }

        if ($viewComponentClass instanceof ViewComponent) {
            return $viewComponentClass;
        }

        return $this->container->get($viewComponentClass);
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

    private function renderTextElement(View $view, TextElement $element): string
    {
        return preg_replace_callback(
            pattern: '/{{\s*(?<eval>\$.*?)\s*}}/',
            callback: function (array $matches) use ($element, $view): string {
                $eval = $matches['eval'];

                if (str_starts_with($eval, '$this->')) {
                    return $view->eval($eval) ?? '';
                }

                return $element->getData()[ltrim($eval, '$')] ?? '';
            },
            subject: $element->getText(),
        );
    }

    private function renderRawElement(RawElement $element): string
    {
        return $element->getHtml();
    }

    private function renderCollectionElement(View $view, CollectionElement $collectionElement): string
    {
        $rendered = [];

        foreach ($collectionElement->getElements() as $element) {
            $rendered[] = $this->renderElement($view, $element);
        }

        return implode(PHP_EOL, $rendered);
    }

    private function renderViewComponent(View $view, ViewComponent $viewComponent, GenericElement $element): string
    {
        $renderedContent = preg_replace_callback(
            pattern: '/<x-slot\s*(name="(?<name>\w+)")?\s*\/>/',
            callback: function ($matches) use ($view, $element) {
                $name = $matches['name'] ?? 'slot';

                $slot = $element->getSlot($name);

                if ($slot === null) {
                    return $matches[0];
                }

                return $this->renderElement($view, $slot);
            },
            subject: $viewComponent->render($element, $this),
        );

        return $this->render(new ViewComponentView(
            wrappingView: $view,
            wrappingElement: $element,
            content: $renderedContent,
        ));
    }

    private function renderEmptyElement(): string
    {
        return '';
    }

    private function renderSlotElement(View $view, SlotElement $element): string
    {
        $rendered = [];

        foreach ($element->getChildren() as $child) {
            $rendered[] = $this->renderElement($view, $child);
        }

        return implode(PHP_EOL, $rendered);
    }

    private function renderGenericElement(View $view, GenericElement $element): string
    {
        $content = [];

        foreach ($element->getChildren() as $child) {
            $content[] = $this->renderElement($view, $child);
        }

        $content = implode(PHP_EOL, $content);

        $attributes = [];

        foreach ($element->getAttributes() as $name => $value) {
            if ($value) {
                $attributes[] = $name . '="' . $value . '"';
            } else {
                $attributes[] = $name;
            }
        }

        $attributes = implode(' ', $attributes);

        if ($attributes !== '') {
            $attributes = ' ' . $attributes;
        }

        return "<{$element->getTag()}{$attributes}>{$content}</{$element->getTag()}>";
    }
}
