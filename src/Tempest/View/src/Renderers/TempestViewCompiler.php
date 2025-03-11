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

    public const array SPECIAL_TAGS = ['html', 'head', 'body'];

    public const string SPECIAL_TAG_PREFIX = 'zzzz_';

    public function __construct(
        private ElementFactory $elementFactory,
        private AttributeFactory $attributeFactory,
        private Kernel $kernel,
    ) {
    }

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
        $template = $this->addSpecialTagPrefixes($template);

        $template = $this->cleanupTemplate($template)->toString();

        $parserFlags = LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR | HTML_NO_DEFAULT_NS;

        $dom = HTMLDocument::createFromString($template, $parserFlags);

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

    private function addSpecialTagPrefixes(string $template): string
    {
        $htmlBlocks = $this->splitHtmlBySpecialTags($template);

        $placeholderTags = $this->resolvePlaceholderTags($htmlBlocks);

        return arr($htmlBlocks)
            ->map(fn (string $part, int $index) => $placeholderTags[$index] ?? $part)
            ->implode('')
            ->toString();
    }

    private function splitHtmlBySpecialTags(string $template): array
    {
        // split $html by <html>, <head> and <body>
        $tagRegexOptions = implode('|', self::SPECIAL_TAGS);
        $regex = "%(</?(?:{$tagRegexOptions})(?:\s[^>]*>|>))%i";
        return preg_split($regex, $template, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @param string[] $htmlBlocks
     * @return array<int,string>
     */
    private function resolvePlaceholderTags(array $htmlBlocks): array
    {
        $parserFlags = LIBXML_NOERROR | HTML_NO_DEFAULT_NS;

        $originalDom = HTMLDocument::createFromString(implode( '', $htmlBlocks), $parserFlags);
        $originalDomCount = $this->countDomNodes($originalDom);

        $tagRegexOptions = implode('|', self::SPECIAL_TAGS);
        $regex = "%(</?)({$tagRegexOptions})(?:\s|>)%i";

        $placeholderTags = [];
        foreach ($htmlBlocks as $index => $part) {

            // only consider relevant tags
            if (!preg_match($regex, $part, $matches)) {
                continue;
            }

            if (!$this->isASpecialTag($htmlBlocks, $index, $originalDomCount, $parserFlags)) {
                continue;
            }

            $opening = $matches[1]; // "<" or "</"
            $tag = $matches[2];     // "html" / "head" / "body"
            $prefix = $opening . self::SPECIAL_TAG_PREFIX . "{$tag}"; // e.g. "</zzz_html" or "<zzz_html"
            $rest = mb_substr($part, mb_strlen("{$opening}$tag")); // e.g. ">", or " class='xxx'>"

            $newTag = $prefix . $rest;

            $placeholderTags[$index] = $newTag;
        }

        return $placeholderTags;
    }

    /**
     * @param string[] $htmlBlocks
     */
    private function isASpecialTag(array $htmlBlocks, int $index, int $originalDomCount, int $parserFlags): bool
    {
        $htmlBlocks[$index] = '<br />'; // some legitimate tag
        $alteredHtml = implode('', $htmlBlocks);
        $alteredDom = HTMLDocument::createFromString($alteredHtml, $parserFlags);

        // remember tags where a new node was added
        return $this->countDomNodes($alteredDom) > $originalDomCount;
    }

    private function countDomNodes(HTMLDocument $dom): int
    {
        return $dom->getElementsByTagName('*')->length;
    }
}
