<?php

declare(strict_types=1);

namespace Tempest\View;

use Exception;
use PHPHtmlParser\Dom;
use Tempest\Application\AppConfig;
use Tempest\View\Elements\ElementFactory;
use function Tempest\path;

final readonly class ViewRenderer
{
    public function __construct(
        private ElementFactory $elementFactory,
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
        $dom->load($contents);

        $elements = [];

        foreach($dom->root->getChildren() as $child) {
            $elements[] = $this->elementFactory->make($view, $child);
        }

       return trim($this->renderElements($elements));
    }

    /**
     * @param \Tempest\View\Elements\GenericElement[] $elements
     */
    private function renderElements(array $elements): string
    {
        $rendered = [];

        foreach ($elements as $element) {
            $rendered[] = $element->render($this);
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
}
