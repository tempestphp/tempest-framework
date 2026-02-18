<?php

declare(strict_types=1);

namespace Tempest\View\Parser;

use Tempest\Discovery\DiscoveryLocation;
use Tempest\Support\Filesystem;
use Tempest\View\Attribute;
use Tempest\View\Attributes\AttributeFactory;
use Tempest\View\CompiledView;
use Tempest\View\Element;
use Tempest\View\Elements\ElementFactory;
use Tempest\View\Elements\RootElement;
use Tempest\View\Exceptions\ViewNotFound;
use Tempest\View\Exceptions\XmlDeclarationCouldNotBeParsed;
use Tempest\View\ShouldBeRemoved;
use Tempest\View\View;
use Tempest\View\WithToken;
use Tempest\View\WrapsElement;

use function Tempest\Support\arr;
use function Tempest\Support\path;
use function Tempest\Support\str;

final readonly class TempestViewCompiler
{
    public const array PHP_TOKENS = [
        '<?php',
        '<?=',
        '?>',
    ];

    private const string SOURCE_PATH_MARKER = '__tempest_source_path=';

    private const string SOURCE_LINE_MARKER = '__tempest_source_line=';

    public function __construct(
        private ElementFactory $elementFactory,
        private AttributeFactory $attributeFactory,
        /** @var DiscoveryLocation[] */
        private array $discoveryLocations = [],
    ) {}

    public function compile(string|View $view): string
    {
        return $this->compileWithSourceMap($view)->content;
    }

    public function compileWithSourceMap(string|View $view, ?string $sourcePath = null, array $prependImports = []): CompiledView
    {
        $this->elementFactory->setViewCompiler($this);

        $prependImports = $this->normalizeImports($prependImports);

        // 1. Retrieve template
        [$template, $resolvedSourcePath] = $this->retrieveTemplate($view);
        $sourcePath ??= $resolvedSourcePath;

        // Check for XML declarations when short_open_tag is enabled
        if (ini_get('short_open_tag') && str_contains($template, needle: '<?xml')) {
            throw new XmlDeclarationCouldNotBeParsed();
        }

        // 2. Remove comments before parsing
        $template = $this->removeComments($template);

        // 3. Parse AST
        $ast = $this->parseAst($template, $sourcePath);

        // 4. Map to elements
        $rootElement = $this->mapToElements($ast);

        if ($prependImports !== []) {
            $rootElement->setInheritedImports($prependImports);
        }

        // 5. Apply attributes
        $rootElement = $this->applyAttributes($rootElement);

        // 6. Compile to PHP
        $compiled = $this->compileElement($rootElement);

        // 7. Cleanup compiled PHP
        [$cleaned, $lineMap] = $this->cleanupCompiled($compiled, $sourcePath, $rootElement->getImports());

        return new CompiledView(
            content: $cleaned,
            sourcePath: $sourcePath,
            lineMap: $lineMap,
        );
    }

    private function removeComments(string $template): string
    {
        return str($template)
            ->replaceRegex('/{{--.*?--}}/s', '')
            ->toString();
    }

    /** @return array{string, string|null} */
    private function retrieveTemplate(string|View $view): array
    {
        $path = $view instanceof View ? $view->path : $view;

        if (! str_ends_with($path, '.php')) {
            return [$path, null];
        }

        $searchPathOptions = [
            $path,
        ];

        if ($view instanceof View && $view->relativeRootPath !== null) {
            $searchPathOptions[] = path($view->relativeRootPath, $path)->toString();
        }

        $searchPathOptions = [
            ...$searchPathOptions,
            ...arr($this->discoveryLocations)
                ->map(fn (DiscoveryLocation $discoveryLocation) => path($discoveryLocation->path, $path)->toString())
                ->toArray(),
        ];

        $searchPath = null;

        foreach ($searchPathOptions as $searchPath) {
            if (Filesystem\is_file($searchPath)) {
                break;
            }
        }

        if (! $searchPath || ! Filesystem\is_file($searchPath)) {
            throw new ViewNotFound($path);
        }

        return [Filesystem\read_file($searchPath), $searchPath];
    }

    private function parseAst(string $template, ?string $sourcePath = null): TempestViewAst
    {
        $tokens = new TempestViewLexer($template, $sourcePath)->lex();

        return new TempestViewParser($tokens)->parse();
    }

    private function mapToElements(TempestViewAst $ast): RootElement
    {
        $elementFactory = $this->elementFactory->withIsHtml($ast->isHtml);

        $rootElement = new RootElement();

        foreach ($ast as $token) {
            $elementFactory->make($token, $rootElement);
        }

        return $rootElement;
    }

    private function applyAttributes(Element $parentElement): Element
    {
        $appliedElements = [];

        $previous = null;

        foreach ($parentElement->getChildren() as $childElement) {
            $this->applyAttributes($childElement);

            $childElement->setPrevious($previous);

            $shouldBeRemoved = false;

            foreach ($childElement->getAttributes() as $name => $value) {
                // TODO: possibly refactor attribute construction to ElementFactory?
                if ($value instanceof Attribute) {
                    $attribute = $value;
                } else {
                    $attribute = $this->attributeFactory->make($name);
                }

                $childElement = $attribute->apply($childElement);

                if ($shouldBeRemoved === false && $attribute instanceof ShouldBeRemoved) {
                    $shouldBeRemoved = true;
                }
            }

            if ($shouldBeRemoved) {
                continue;
            }

            $appliedElements[] = $childElement;

            $previous = $childElement;
        }

        $parentElement->setChildren($appliedElements);

        return $parentElement;
    }

    public function compileElement(Element $rootElement): string
    {
        $compiled = arr();
        $sourcePath = null;

        foreach ($rootElement->getChildren() as $element) {
            if ($sourceLocation = $this->resolveSourceLocation($element)) {
                if ($sourceLocation['sourcePath'] !== $sourcePath) {
                    $sourcePath = $sourceLocation['sourcePath'];

                    if ($sourcePath !== null) {
                        $compiled[] = self::sourcePathMarker($sourcePath);
                    }
                }

                $compiled[] = self::sourceLineMarker($sourceLocation['sourceLine']);
            }

            $compiled[] = $element->compile();
        }

        return $compiled
            ->implode(PHP_EOL)
            ->toString();
    }

    private static function sourcePathMarker(string $sourcePath): string
    {
        return sprintf('<?php /*%s%s*/ ?>', self::SOURCE_PATH_MARKER, base64_encode($sourcePath));
    }

    private static function sourceLineMarker(int $sourceLine): string
    {
        return sprintf('<?php /*%s%d*/ ?>', self::SOURCE_LINE_MARKER, $sourceLine);
    }

    /** @return array{sourcePath: string|null, sourceLine: int}|null */
    private function resolveSourceLocation(Element $element): ?array
    {
        if ($element instanceof WithToken) {
            return [
                'sourcePath' => $element->token->sourcePath,
                'sourceLine' => $element->token->line,
            ];
        }

        if ($element instanceof WrapsElement) {
            return $this->resolveSourceLocation($element->getWrappingElement());
        }

        foreach ($element->getChildren() as $child) {
            if ($sourceLocation = $this->resolveSourceLocation($child)) {
                return $sourceLocation;
            }
        }

        return null;
    }

    /**
     * @return array{string, array<int, array{compiledStartLine: int, compiledEndLine: int, sourcePath: string, sourceStartLine: int}>}
     */
    private function cleanupCompiled(string $compiled, ?string $sourcePath, array $importsToPrepend = []): array
    {
        // Remove strict type declarations
        $compiled = str($compiled)->replace('declare(strict_types=1);', '');

        // Cleanup and bundle imports
        $imports = arr();

        foreach ($this->normalizeImports($importsToPrepend) as $import) {
            $imports[$import] = $import;
        }

        $compiled = $compiled->replaceRegex("/^\s*use (function )?.*;/m", function (array $matches) use (&$imports) {
            // The import contains escaped slashes, meaning it's a var_exported string; we can ignore those
            if (str_contains($matches[0], '\\\\')) {
                return $matches[0];
            }

            $import = trim($matches[0]);

            $imports[$import] = $import;

            return '';
        });

        $compiled = $compiled->prepend(
            sprintf(
                '<?php
%s
?>',
                $imports->implode(PHP_EOL),
            ),
        );

        // Remove empty PHP blocks
        $compiled = $compiled->replaceRegex('/<\?php\s*\?>/', '');

        return $this->extractSourceMap($compiled->toString(), $sourcePath);
    }

    private function normalizeImports(array $imports): array
    {
        $normalized = [];

        foreach ($imports as $import) {
            $import = trim($import);

            if ($import === '') {
                continue;
            }

            $normalized[$import] = $import;
        }

        return array_values($normalized);
    }

    /**
     * @return array{string, array<int, array{compiledStartLine: int, compiledEndLine: int, sourcePath: string, sourceStartLine: int}>}
     */
    private function extractSourceMap(string $compiled, ?string $sourcePath): array
    {
        $compiledLines = explode("\n", $compiled);

        $cleanedLines = [];
        $lineMap = [];
        $currentSourcePath = $sourcePath;
        $sourceLine = null;
        $compiledLine = 0;

        foreach ($compiledLines as $line) {
            if (preg_match(sprintf('/^\s*<\?php \/\*%s(?<path>[a-zA-Z0-9\+\/=]+)\*\/ \?>\s*$/', self::SOURCE_PATH_MARKER), $line, $matches) === 1) {
                $decodedPath = base64_decode($matches['path'], true);
                $currentSourcePath = $decodedPath === false ? null : $decodedPath;
                continue;
            }

            if (preg_match(sprintf('/^\s*<\?php \/\*%s(?<line>\d+)\*\/ \?>\s*$/', self::SOURCE_LINE_MARKER), $line, $matches) === 1) {
                $sourceLine = $currentSourcePath !== null
                    ? (int) $matches['line']
                    : null;

                continue;
            }

            $compiledLine++;
            $cleanedLines[] = $line;

            if ($sourceLine === null || $currentSourcePath === null) {
                continue;
            }

            $lineMap[$compiledLine] = [
                'sourcePath' => $currentSourcePath,
                'sourceLine' => $sourceLine,
            ];

            $sourceLine++;
        }

        return [
            implode("\n", $cleanedLines),
            $this->compressLineMap($lineMap),
        ];
    }

    /**
     * @param array<int, array{sourcePath: string, sourceLine: int}> $lineMap
     * @return array<int, array{compiledStartLine: int, compiledEndLine: int, sourcePath: string, sourceStartLine: int}>
     */
    private function compressLineMap(array $lineMap): array
    {
        if ($lineMap === []) {
            return [];
        }

        ksort($lineMap);

        $entries = [];
        $currentRange = null;

        foreach ($lineMap as $compiledLine => $sourceLocation) {
            $lineMapping = $this->createLineMapping($compiledLine, $sourceLocation);

            if ($currentRange === null) {
                $currentRange = $this->startLineMapRange($lineMapping);
                continue;
            }

            if ($this->canExtendLineMapRange($currentRange, $lineMapping)) {
                $currentRange = $this->extendLineMapRange($currentRange, $lineMapping);
                continue;
            }

            $entries[] = $this->createLineMapEntry($currentRange);
            $currentRange = $this->startLineMapRange($lineMapping);
        }

        $entries[] = $this->createLineMapEntry($currentRange);

        return $entries;
    }

    /**
     * @param array{sourcePath: string, sourceLine: int} $sourceLocation
     * @return array{compiledLine: int, sourcePath: string, sourceLine: int}
     */
    private function createLineMapping(int $compiledLine, array $sourceLocation): array
    {
        return [
            'compiledLine' => $compiledLine,
            'sourcePath' => $sourceLocation['sourcePath'],
            'sourceLine' => $sourceLocation['sourceLine'],
        ];
    }

    /**
     * @param array{compiledLine: int, sourcePath: string, sourceLine: int} $lineMapping
     * @return array{compiledStartLine: int, compiledEndLine: int, sourcePath: string, sourceStartLine: int, sourceEndLine: int}
     */
    private function startLineMapRange(array $lineMapping): array
    {
        return [
            'compiledStartLine' => $lineMapping['compiledLine'],
            'compiledEndLine' => $lineMapping['compiledLine'],
            'sourcePath' => $lineMapping['sourcePath'],
            'sourceStartLine' => $lineMapping['sourceLine'],
            'sourceEndLine' => $lineMapping['sourceLine'],
        ];
    }

    /**
     * @param array{compiledStartLine: int, compiledEndLine: int, sourcePath: string, sourceStartLine: int, sourceEndLine: int} $range
     * @param array{compiledLine: int, sourcePath: string, sourceLine: int} $lineMapping
     */
    private function canExtendLineMapRange(array $range, array $lineMapping): bool
    {
        return (
            $lineMapping['compiledLine']
            === ($range['compiledEndLine'] + 1)
            && $lineMapping['sourcePath'] === $range['sourcePath']
            && $lineMapping['sourceLine']
            === ($range['sourceEndLine'] + 1)
        );
    }

    /**
     * @param array{compiledStartLine: int, compiledEndLine: int, sourcePath: string, sourceStartLine: int, sourceEndLine: int} $range
     * @param array{compiledLine: int, sourcePath: string, sourceLine: int} $lineMapping
     * @return array{compiledStartLine: int, compiledEndLine: int, sourcePath: string, sourceStartLine: int, sourceEndLine: int}
     */
    private function extendLineMapRange(array $range, array $lineMapping): array
    {
        $range['compiledEndLine'] = $lineMapping['compiledLine'];
        $range['sourceEndLine'] = $lineMapping['sourceLine'];

        return $range;
    }

    /**
     * @param array{compiledStartLine: int, compiledEndLine: int, sourcePath: string, sourceStartLine: int, sourceEndLine: int} $range
     * @return array{compiledStartLine: int, compiledEndLine: int, sourcePath: string, sourceStartLine: int}
     */
    private function createLineMapEntry(array $range): array
    {
        return [
            'compiledStartLine' => $range['compiledStartLine'],
            'compiledEndLine' => $range['compiledEndLine'],
            'sourcePath' => $range['sourcePath'],
            'sourceStartLine' => $range['sourceStartLine'],
        ];
    }
}
