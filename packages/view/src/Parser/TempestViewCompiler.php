<?php

declare(strict_types=1);

namespace Tempest\View\Parser;

use Tempest\Core\Kernel;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Support\Filesystem;
use Tempest\View\Attribute;
use Tempest\View\Attributes\AttributeFactory;
use Tempest\View\Attributes\ElseAttribute;
use Tempest\View\Components\DynamicViewComponent;
use Tempest\View\Element;
use Tempest\View\Elements\ElementFactory;
use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\Exceptions\ViewNotFound;
use Tempest\View\ShouldBeRemoved;
use Tempest\View\View;

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

        // 2. Remove comments before parsing
        $template = $this->removeComments($template);

        // 3. Parse AST
        $ast = $this->parseAst($template);

        // 4. Map to elements
        $elements = $this->mapToElements($ast);

        // 5. Apply attributes
        $elements = $this->applyAttributes($elements);

        // 6. Compile to PHP
        $compiled = $this->compileElements($elements);

        // 7. Cleanup compiled PHP
        $cleaned = $this->cleanupCompiled($compiled);

        return $cleaned;
    }

    private function removeComments(string $template): string
    {
        return str($template)
            ->replaceRegex('/{{--.*?--}}/s', '')
            ->toString();
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
            if (Filesystem\is_file($searchPath)) {
                break;
            }
        }

        if (! Filesystem\is_file($searchPath)) {
            throw new ViewNotFound($path);
        }

        return Filesystem\read_file($searchPath);
    }

    private function parseAst(string $template): TempestViewAst
    {
        $tokens = new TempestViewLexer($template)->lex();

        return new TempestViewParser($tokens)->parse();
    }

    /**
     * @return Element[]
     */
    private function mapToElements(TempestViewAst $ast): array
    {
        $elements = [];

        foreach ($ast as $token) {
            $element = $this->elementFactory->make($token);

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
            $isDynamicViewComponent = $element instanceof ViewComponentElement && $element->getViewComponent() instanceof DynamicViewComponent;

            if (! $isDynamicViewComponent) {
                $children = $this->applyAttributes($element->getChildren());
                $element->setChildren($children);
            }

            $element->setPrevious($previous);

            $shouldBeRemoved = false;

            foreach ($element->getAttributes() as $name => $value) {
                if ($isDynamicViewComponent && $name !== ':is' && $name !== 'is') {
                    continue;
                }

                // TODO: possibly refactor attribute construction to ElementFactory?
                if ($value instanceof Attribute) {
                    $attribute = $value;
                } else {
                    $attribute = $this->attributeFactory->make($name);
                }

                $element = $attribute->apply($element);

                if ($shouldBeRemoved === false && $attribute instanceof ShouldBeRemoved) {
                    $shouldBeRemoved = true;
                }
            }

            if ($shouldBeRemoved) {
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
            ->toString();
    }

    private function cleanupCompiled(string $compiled): string
    {
        // Remove strict type declarations
        $compiled = str($compiled)->replace('declare(strict_types=1);', '');

        // Cleanup and bundle imports
        $imports = arr();

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

        return $compiled->toString();
    }
}
