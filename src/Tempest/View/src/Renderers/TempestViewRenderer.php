<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

use DOMNodeList;
use Exception;
use Masterminds\HTML5;
use ParseError;
use Tempest\Core\Kernel;
use function Tempest\path;
use Tempest\View\Attributes\AttributeFactory;
use Tempest\View\Element;
use Tempest\View\Elements\ElementFactory;
use Tempest\View\GenericView;
use Tempest\View\View;
use Tempest\View\ViewCache;
use Tempest\View\ViewRenderer;

final class TempestViewRenderer implements ViewRenderer
{
    private const array TOKEN_MAPPING = [
        '<?php' => '__TOKEN_PHP_OPEN__',
        '<?=' => '__TOKEN_PHP_SHORT_ECHO__',
        '?>' => '__TOKEN_PHP_CLOSE__',
    ];

    private ?View $currentView = null;

    public function __construct(
        private readonly ElementFactory $elementFactory,
        private readonly AttributeFactory $attributeFactory,
        private readonly Kernel $kernel,
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
        // 1. Retrieve template
        $template = $this->retrieveTemplate($view);

        // 2. Parse as DOM
        $dom = $this->parseDom($template);

        // 3. Map to elements
        $elements = $this->mapToElements($dom);

        // 4. Apply attributes
        $elements = $this->applyAttributes($elements);

        // 5. Compile to PHP
        $compiled = $this->compileElements($elements);

        // (6. cache)
        // TODO

        // 7. Render compiled template
        return $this->renderCompiled($view, $compiled);
    }

    private function retrieveTemplate(View|string|null $view): string
    {
        if ($view === null) {
            $view = '';
        }

        if (is_string($view)) {
            $view = new GenericView($view);
        }

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

    private function parseDom(string $template): DOMNodeList
    {
        $template = str_replace(
            search: array_keys(self::TOKEN_MAPPING),
            replace: array_values(self::TOKEN_MAPPING),
            subject: $template,
        );

        $html5 = new HTML5();

        $dom = $html5->loadHTML("<div id='tempest_render'>{$template}</div>");

        return $dom->getElementById('tempest_render')->childNodes;
    }

    /**
     * @return Element[]
     */
    private function mapToElements(DOMNodeList $domNodeList): array
    {
        $elements = [];

        foreach ($domNodeList as $node) {
            $element = $this->elementFactory->make($node);

            if ($element === null) {
                continue;
            }

            $elements[] = $element;
        }

        return $elements;
    }

    /**
     * @param Element[] $elements
     * @return Element[]
     */
    private function applyAttributes(array $elements): array
    {
        $appliedElements = [];

        $previous = null;

        foreach ($elements as $element) {
            $children = $this->applyAttributes($element->getChildren());

            $element
                ->setPrevious($previous)
                ->setChildren($children);

            foreach ($element->getAttributes() as $name => $value) {
                $attribute = $this->attributeFactory->make($name);

                $element = $attribute->apply($element);

                if ($element === null) {
                    break;
                }
            }

            if ($element === null) {
                continue;
            }

            $appliedElements[] = $element;

            $previous = $element;
        }

        return $appliedElements;
    }

    /** @param \Tempest\View\Element[] $elements */
    private function compileElements(array $elements): string
    {
        $compiled = [];

        foreach ($elements as $element) {
            $compiled[] = $element->compile();
        }

        $compiled = implode(PHP_EOL, $compiled);

        return str_replace(
            search: array_values(self::TOKEN_MAPPING),
            replace: array_keys(self::TOKEN_MAPPING),
            subject: $compiled,
        );
    }

    private function renderCompiled(View $_view, string $_content): string
    {
        $this->currentView = $_view;

        ob_start();

        // Extract data from view into local variables so that they can be accessed directly
        $_data = $_view->getData();

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
            if (! $_view->has($key)) {
                $_view->data(...[$key => $value]);
            }
        }

        $this->currentView = null;

        return trim(ob_get_clean());
    }
}
