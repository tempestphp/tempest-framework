<?php

declare(strict_types=1);

namespace Tempest\View\Parser;

use Tempest\Core\Kernel;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Mapper\Exceptions\ViewNotFound;
use Tempest\View\Attribute;
use Tempest\View\Attributes\AttributeFactory;
use Tempest\View\Element;
use Tempest\View\Elements\ElementFactory;
use Tempest\View\View;
use function Tempest\Support\arr;
use function Tempest\Support\path;

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

        // 2. Parse AST
        $ast = $this->parseAst($template);

        // 3. Map to elements
        $elements = $this->mapToElements($ast);

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
            $children = $this->applyAttributes($element->getChildren());

            $element
                ->setPrevious($previous)
                ->setChildren($children);

            foreach ($element->getAttributes() as $name => $value) {
                // TODO: possibly refactor attribute construction to ElementFactory?
                if ($value instanceof Attribute) {
                    $attribute = $value;
                } else {
                    $attribute = $this->attributeFactory->make($name);
                }

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
            ->toString();
    }
}
