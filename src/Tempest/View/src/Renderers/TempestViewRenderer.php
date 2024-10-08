<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

use Exception;
use Masterminds\HTML5;
use ParseError;
use Tempest\Container\Container;
use Tempest\Core\Kernel;
use function Tempest\path;
use function Tempest\Support\arr;
use Tempest\View\Attributes\AttributeFactory;
use Tempest\View\Element;
use Tempest\View\Elements\CollectionElement;
use Tempest\View\Elements\ElementFactory;
use Tempest\View\Elements\EmptyElement;
use Tempest\View\Elements\GenericElement;
use Tempest\View\Elements\RawElement;
use Tempest\View\Elements\SlotElement;
use Tempest\View\Elements\TextElement;
use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\GenericView;
use Tempest\View\View;
use Tempest\View\ViewCache;
use Tempest\View\ViewConfig;
use Tempest\View\ViewRenderer;

final class TempestViewRenderer implements ViewRenderer
{
    private const array TOKEN_MAPPING = [
        '<?php' => '__TOKEN_PHP_OPEN__',
        '<?=' => '__TOKEN_PHP_SHORT_ECHO__',
        '?>' => '__TOKEN_PHP_CLOSE__',
    ];

    private ?View $currentView = null;

    private ?Element $currentScope = null;

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
        return $this->currentScope?->getData($name)
            ?? $this->currentView?->get($name);
    }

    public function __call(string $name, array $arguments)
    {
        return $this->currentView?->{$name}(...$arguments);
    }

    public function render(string|View|null $view): string
    {
        $view = $this->resolveView($view);

        //        /** @var Element $element */
        //        $element = $this->viewCache->resolve(
        //            key: (string)crc32($view->getPath()),
        //            cache: function () use ($view) {
        $contents = $this->resolveContent($view);

        $contents = str_replace(
            search: array_keys(self::TOKEN_MAPPING),
            replace: array_values(self::TOKEN_MAPPING),
            subject: $contents,
        );

        $html5 = new HTML5();
        $dom = $html5->loadHTML("<div id='tempest_render'>{$contents}</div>");

        $element = $this->elementFactory->make(
            $dom->getElementById('tempest_render'),
        );

        //                return $element;
        //            },
        //        );

        $element->setView($view);

        $element = $this->applyAttributes(
            view: $view,
            element: $element,
        );

        $rendered = $this->renderElements($view, $element->getChildren());

        $this->currentScope = null;

        $rendered = str_replace(
            search: array_values(self::TOKEN_MAPPING),
            replace: array_keys(self::TOKEN_MAPPING),
            subject: $rendered,
        );

        return $this->scopedEval($rendered);
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

        if ($element instanceof ViewComponentElement) {
            return $this->renderViewComponentElement($view, $element);
        }

        if ($element instanceof GenericElement) {
            return $this->renderGenericElement($view, $element);
        }

        $this->currentScope = null;

        throw new Exception("No rendered found");
    }

    private function resolveContent(View $view): string
    {
        $path = $view->getPath();

        if (! str_ends_with($path, '.php')) {
            return $path;
        }

        $discoveryLocations = $this->kernel->discoveryLocations;

        while (! file_exists($path) && $location = current($discoveryLocations)) {
            $path = path($location->path, $view->getPath());
            next($discoveryLocations);
        }

        if (! file_exists($path)) {
            throw new Exception("View {$path} not found");
        }

        return file_get_contents($path);
    }

    private function applyAttributes(View $view, Element $element): Element
    {
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

    private function renderViewComponentElement(View $view, ViewComponentElement $element): string
    {
        $this->currentScope = $element;

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
            subject: $element->getViewComponent()->render($element, $this),
        );

        $rendered = $this->scopedEval($renderedContent);

        $this->currentScope = null;

        return $rendered;
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

    private function scopedEval(string $_content): string
    {
        ob_start();

        // Extract data from current element and current view into local variables so that they can be accessed directly
        $_data = arr([
            ...($this->currentScope?->getData() ?? []),
            ...$this->currentView?->getData(),
        ])
            ->mapWithKeys(fn (mixed $value, string $key) => yield ltrim($key, ':') => $value)
            ->toArray();

        extract($_data, flags: EXTR_SKIP);

        // Cleanup content before parsing
        $_content = str_replace('declare(strict_types=1);', '', $_content);

        try {
            /** @phpstan-ignore-next-line */
            eval('?>' . $_content . '<?php');
        } catch (ParseError) {
            return $_content;
        }

        // If the view defines local variables, we add them here to the view object as well
        foreach (get_defined_vars() as $key => $value) {
            if (! $this->currentView->has($key)) {
                $this->currentView->data(...[$key => $value]);
            }
        }

        return trim(ob_get_clean());
    }

    private function resolveView(View|string|null $view): View
    {
        if ($view === null) {
            $view = '';
        }

        if (is_string($view)) {
            $view = new GenericView($view);
        }

        $this->currentView = $view;

        return $view;
    }
}
