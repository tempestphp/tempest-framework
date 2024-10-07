<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

use Exception;
use Masterminds\HTML5;
use ParseError;
use Tempest\Container\Container;
use Tempest\Core\Kernel;
use Tempest\View\ViewCache;
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
        private readonly Kernel $kernel,
        private readonly ViewConfig $viewConfig,
        private readonly Container $container,
        private readonly ViewCache $viewCache,
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

        $element = $this->viewCache->resolve(
            key: (string) crc32($view->getPath()),
            cache: function () use ($view) {
                $contents = $this->resolveContent($view);

                $html5 = new HTML5();
                $dom = $html5->loadHTML("<div id='tempest_render'>{$contents}</div>");

                return $this->elementFactory->make(
                    $view,
                    $dom->getElementById('tempest_render'),
                );
            }
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
            return $this->evalContentIsolated($view, $path);
        }

        $discoveryLocations = $this->kernel->discoveryLocations;

        while (! file_exists($path) && $location = current($discoveryLocations)) {
            $path = path($location->path, $view->getPath());
            next($discoveryLocations);
        }

        if (! file_exists($path)) {
            throw new Exception("View {$path} not found");
        }

        return $this->resolveContentIsolated($view, $path);
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
            pattern: '/<x-slot\s*(name="(?<name>\w+)")?((\s*\/>)|><\/x-slot>)/',
            callback: function ($matches) use ($view, $element) {
                $name = $matches['name'] ?: 'slot';

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

        $content = implode('', $content);

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

    private function resolveContentIsolated(View $_view, string $_path): string
    {
        ob_start();

        $_data = $_view->getData();

        extract($_data, flags: EXTR_SKIP);

        include $_path;

        $content = ob_get_clean();

        // If the view defines local variables, we add them here to the view object as well
        foreach (get_defined_vars() as $key => $value) {
            if (! $_view->has($key)) {
                $_view->data(...[$key => $value]);
            }
        }

        return $content;
    }

    private function evalContentIsolated(View $_view, string $_content): string
    {
        ob_start();

        $_data = $_view->getData();

        extract($_data, flags: EXTR_SKIP);

        try {
            // TODO: find a better way of dealing with views that declare strict types
            $_content = str_replace('declare(strict_types=1);', '', $_content);

            /** @phpstan-ignore-next-line */
            eval('?>' . $_content . '<?php');
        } catch (ParseError) {
            return $_content;
        }

        // If the view defines local variables, we add them here to the view object as well
        foreach (get_defined_vars() as $key => $value) {
            if (! $_view->has($key)) {
                $_view->data(...[$key => $value]);
            }
        }

        return ob_get_clean();
    }
}
