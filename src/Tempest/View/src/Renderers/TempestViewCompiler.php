<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

use Dom\HTMLDocument;
use Dom\NodeList;
use Exception;
use Tempest\Core\Kernel;
use Tempest\View\Attributes\AttributeFactory;
use Tempest\View\Element;
use Tempest\View\Elements\ElementFactory;
use function Tempest\path;
use function Tempest\Support\str;
use const Dom\HTML_NO_DEFAULT_NS;

final readonly class TempestViewCompiler
{
    public const string TOKEN_PHP_OPEN = '<!--TOKEN_PHP_OPEN__';

    public const string TOKEN_PHP_SHORT_ECHO = '<!--TOKEN_PHP_SHORT_ECHO__';

    public const string TOKEN_PHP_CLOSE = '__TOKEN_PHP_CLOSE-->';

    public const array TOKEN_MAPPING = [
        '<?php' => self::TOKEN_PHP_OPEN,
        '<?=' => self::TOKEN_PHP_SHORT_ECHO,
        '?>' => self::TOKEN_PHP_CLOSE,
    ];

    public function __construct(
        private ElementFactory $elementFactory,
        private AttributeFactory $attributeFactory,
        private Kernel $kernel,
    ) {}

    public function compile(string $path): string
    {
        $this->elementFactory->setViewCompiler($this);

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

        $searchPath = $path;

        while (! file_exists($searchPath) && $location = current($discoveryLocations)) {
            $searchPath = path($location->path, $path)->toString();
            next($discoveryLocations);
        }

        if (! file_exists($searchPath)) {
            throw new Exception("View {$searchPath} not found");
        }

        return file_get_contents($searchPath);
    }

    private function parseDom(string $template): HTMLDocument|NodeList
    {
        $template = str($template)

            // Escape PHP tags
            ->replace(
                search: array_keys(self::TOKEN_MAPPING),
                replace: array_values(self::TOKEN_MAPPING),
            )

            // Convert self-closing tags
            ->replaceRegex(
                regex: '/<x-(?<element>.*?)\/>/',
                replace: function (array $match) {
                    $closingTag = str($match['element'])->before(' ')->toString();

                    return sprintf(
                        '<x-%s></x-%s>',
                        $match['element'],
                        $closingTag,
                    );
                },
            );

        $isFullHtmlDocument = $template
            ->replaceRegex('/('.self::TOKEN_PHP_OPEN.'|'.self::TOKEN_PHP_SHORT_ECHO.')(.|\n)*?'.self::TOKEN_PHP_CLOSE.'/', '')
            ->trim()
            ->startsWith(['<html', '<!DOCTYPE', '<!doctype']);

        $parserFlags = LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR | HTML_NO_DEFAULT_NS;

        if ($isFullHtmlDocument) {
            // If we're rendering a full HTML document, we'll parse it as is
            return HTMLDocument::createFromString($template->toString(), $parserFlags);
        }

        // If we're rendering an HTML snippet, we'll wrap it in a div, and return the resulting nodelist
        $dom = HTMLDocument::createFromString("<div id='tempest_render'>{$template}</div>", $parserFlags);

        return $dom->getElementById('tempest_render')->childNodes;
    }

    /**
     * @return Element[]
     */
    private function mapToElements(HTMLDocument|NodeList $nodes): array
    {
        $elements = [];

        if ($nodes instanceof HTMLDocument) {
            $nodes = $nodes->childNodes;
        }

        foreach ($nodes as $node) {
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
                $attribute = $this->attributeFactory->make($element, $name);

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
