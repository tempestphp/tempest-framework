<?php

declare(strict_types=1);

namespace Tempest\View;

use Exception;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\AbstractNode;
use PHPHtmlParser\Dom\InnerNode;
use ReflectionClass;
use ReflectionException;
use Tempest\Application\AppConfig;
use function Tempest\get;
use function Tempest\path;
use function Tempest\type;

final class ViewRenderer
{
    private ?View $currentView = null;

    public function __construct(
        private readonly AppConfig $appConfig,
        private readonly ViewConfig $viewConfig,
    ) {}

    public function __get(string $name): mixed
    {
        return $this->currentView?->get($name);
    }

    public function __set(string $name, $value): void
    {
        if (! $this->currentView) {
            return;
        }

        $this->currentView->{$name} = $value;
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->currentView?->{$name}(...$arguments);
    }

    public function render(View $view): string
    {
        $this->currentView = $view;

        $contents = $this->resolveContent($view);

        $dom = new Dom();
        $dom->load($contents);

        $elements = [];

        foreach($dom->root->getChildren() as $child) {
            $elements[] = $this->resolveElement($child);
        }

       return $this->renderElements($elements);
    }

    /**
     * @param \Tempest\View\Element[] $elements
     */
    private function renderElements(array $elements): string
    {
        $rendered = [];

        foreach ($elements as $element) {
            if ($viewComponent = $this->resolveViewComponent($element)) {
                $rendered[] = $viewComponent->render($this->renderElements($element->getChildren()));
            } else {
                $rendered[] = $element->render();
            }
        }

        return implode('', $rendered);
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

    private function resolveElement(AbstractNode $node): Element
    {
        $children = [];

        if ($node instanceof InnerNode) {
            foreach ($node->getChildren() as $child)
            {
                $children[] = $this->resolveElement($child);
            }
        }

        return new Element(
            html: $node->outerHtml(),
            tag: $node->getTag()->name(),
            attributes: $node->getAttributes(),
            children: $children,
        );
    }

    private function resolveViewComponent(Element $element): ?ViewComponent
    {
        /** @var class-string<\Tempest\View\ViewComponent>|null $component */
        $componentClass = $this->viewConfig->viewComponents[$element->getTag()] ?? null;

        if (! $componentClass) {
            return null;
        }

        if (! $componentClass instanceof ViewComponent) {
            $attributes = [
                'view' => $this->currentView,
            ];

            foreach ($element->getAttributes() as $name => $value) {
                if (str_starts_with($name, ':')) {
                    /** @phpstan-ignore-next-line */
                    $value = eval("return {$value};");
                    $name = substr($name, 1);
                }

                $attributes[$name] = $value;
            }

            $reflection = new ReflectionClass($componentClass);

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
                        throw new Exception("Could not resolve value for field {$componentClass}::\${$parameter->name}");
                    }
                }
            }

            /** @var ViewComponent $component */
            $component = $reflection->newInstance(...$attributesToInject);
        } else {
            $component = $componentClass;
        }
        return $component;
    }
}
