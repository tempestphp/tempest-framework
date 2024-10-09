<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

use DOMNodeList;
use Exception;
use Masterminds\HTML5;
use Tempest\Core\Kernel;
use function Tempest\path;
use function Tempest\Support\arr;
use function Tempest\Support\str;
use Tempest\View\Attributes\AttributeFactory;
use Tempest\View\Element;
use Tempest\View\Elements\ElementFactory;

final readonly class TempestViewCompiler
{
    private const array TOKEN_MAPPING = [
        '<?php' => '__TOKEN_PHP_OPEN__',
        '<?=' => '__TOKEN_PHP_SHORT_ECHO__',
        '?>' => '__TOKEN_PHP_CLOSE__',
    ];

    public function __construct(
        private ElementFactory $elementFactory,
        private AttributeFactory $attributeFactory,
        private Kernel $kernel,
    ) {}

    public function compile(string $path): string
    {
        // 1. Retrieve template
        $template = $this->retrieveTemplate($path);

        // 2. Parse as DOM
        $dom = $this->parseDom($template);

        // 3. Map to elements
        $elements = $this->mapToElements($dom);

        // 4. Apply attributes
        $elements = $this->applyAttributes($elements);

        // 5. Compile to PHP
        $compiled = $this->compileElements($elements);

        return $compiled;
    }

    private function retrieveTemplate(string $path): string
    {
        if (! str_ends_with($path, '.php')) {
            return $path;
        }

        $discoveryLocations = $this->kernel->discoveryLocations;

        while (! file_exists($path) && $location = current($discoveryLocations)) {
            $path = path($location->path, $path);
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
            $element = $this->elementFactory
                ->setViewCompiler($this)
                ->make($node);

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
}
