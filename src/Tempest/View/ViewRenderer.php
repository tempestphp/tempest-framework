<?php

declare(strict_types=1);

namespace Tempest\View;

use Exception;
use PHPHtmlParser\Dom;
use Tempest\Application\AppConfig;
use Tempest\View\Attributes\AttributeFactory;
use Tempest\View\Elements\ElementFactory;
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
            $rendered[] = $element->addData(...$view->getData())->render($this);
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

    private function applyAttributes(View $view, Element $element): Element
    {
        if (! $element instanceof HasAttributes) {
            return $element;
        }

        /** @var \Tempest\View\Element&\Tempest\View\HasAttributes $element */

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
}
