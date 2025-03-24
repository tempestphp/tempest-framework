<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

use Dom\HTMLDocument;
use Dom\NodeList;
use Stringable;
use Tempest\Core\Kernel;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Mapper\Exceptions\ViewNotFound;
use Tempest\Support\Str\ImmutableString;
use Tempest\View\Attributes\AttributeFactory;
use Tempest\View\Element;
use Tempest\View\Elements\ElementFactory;
use Tempest\View\View;

use function Tempest\Support\arr;
use function Tempest\Support\Html\is_void_tag;
use function Tempest\Support\path;
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

    public function compile(string|View $view): string
    {
        $this->elementFactory->setViewCompiler($this);

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

        return $compiled;
    }

    private function retrieveTemplate(string|View $view): string
    {
        $path = ($view instanceof View) ? $view->path : $view;

        if (! str_ends_with($path, '.php')) {
            return $path;
        }

        $searchPathOptions = [
            $path,
        ];

        if ($view instanceof View && $view->relativeRootPath !== null) {
            $searchPathOptions[] = path($view->relativeRootPath, $path)->toString();
        }

        $searchPathOptions = [
            ...$searchPathOptions,
            ...arr($this->kernel->discoveryLocations)
                ->map(fn (DiscoveryLocation $discoveryLocation) => path($discoveryLocation->path, $path)->toString())
                ->toArray(),
        ];

        foreach ($searchPathOptions as $searchPath) {
            if (file_exists($searchPath)) {
                break;
            }
        }

        if (! file_exists($searchPath)) {
            throw new ViewNotFound($path);
        }

        return file_get_contents($searchPath);
    }

    private function parseDom(string $template): NodeList
    {
        $parserFlags = LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR | HTML_NO_DEFAULT_NS;

        $template = str($template)
            // Convert self-closing and void tags
            ->replaceRegex(
                regex: '/<(?<element>\w[^<]*?)\/>/',
                replace: function (array $match) {
                    $element = str($match['element'])->trim();

                    if (is_void_tag($element)) {
                        // Void tags must not have a closing tag
                        return sprintf('<%s>', $element->toString());
                    }

                    // Other self-closing tags must get a proper closing tag
                    return sprintf(
                        '<%s></%s>',
                        $match['element'],
                        $element->before(' ')->toString(),
                    );
                },
            );

        // Find head nodes, these are parsed separately so that we skip HTML's head-parsing rules
        $headNodes = [];

        $headTemplate = $template->match('/<head>((.|\n)*?)<\/head>/')[1] ?? null;

        if ($headTemplate) {
            $headNodes = HTMLDocument::createFromString(
                source: $this->cleanupTemplate($headTemplate)->toString(),
                options: $parserFlags,
            )->childNodes;
        }

        $mainTemplate = $this->cleanupTemplate($template)
            // Cleanup head, we'll insert it after having parsed the DOM
            ->replaceRegex('/<head>((.|\n)*?)<\/head>/', '<head></head>');

        $dom = HTMLDocument::createFromString(
            source: $mainTemplate->toString(),
            options: $parserFlags,
        );

        // If we have head nodes and a head tag, we inject them back
        if (($headElement = $dom->getElementsByTagName('head')->item(0)) !== null) {
            foreach ($headNodes as $headNode) {
                $headElement->appendChild($dom->importNode($headNode, deep: true));
            }
        }

        return $dom->childNodes;
    }

    /**
     * @return Element[]
     */
    private function mapToElements(NodeList $nodes): array
    {
        $elements = [];

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
        $compiled = arr();

        foreach ($elements as $element) {
            $compiled[] = $element->compile();
        }

        return $compiled
            ->implode(PHP_EOL)
            // Unescape PHP tags
            ->replace(
                array_values(self::TOKEN_MAPPING),
                array_keys(self::TOKEN_MAPPING),
            )
            ->toString();
    }

    private function cleanupTemplate(string|Stringable $template): ImmutableString
    {
        return str($template)
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
    }
}
