<?php

namespace Tempest\View\Components;

use Stringable;
use Tempest\Core\AppConfig;
use Tempest\Support\Str\ImmutableString;
use Tempest\View\Elements\CollectionElement;
use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\Parser\TempestViewCompiler;
use Tempest\View\Parser\Token;
use Tempest\View\ViewComponent;
use Tempest\View\ViewConfig;
use function Tempest\Support\arr;

final class DynamicViewComponent implements ViewComponent
{
    private Token $token;

    public function __construct(
        private AppConfig $appConfig,
        private TempestViewCompiler $compiler,
        private ViewConfig $viewConfig,
    ) {}

    public function setToken(Token $token): void
    {
        $this->token = $token;
    }

    public static function getName(): string
    {
        return 'x-dynamic-component';
    }

    public function compile(ViewComponentElement $element): string
    {
        $name = $this->token->getAttribute('is') ?? $this->token->getAttribute(':is');

        $isExpression = $this->token->getAttribute(':is') !== null;

        $collectionElement = new CollectionElement($element->getChildren());

        $attributes = arr($element->getAttributes())
            ->filter(fn (string $value, string $key) => $key !== 'is' && $key !== ':is')
            ->map(function (string $value, string $key) {
                return sprintf('%s="%s"', $key, trim($value));
            })
            ->implode(' ')
            ->when(
                fn (Stringable $string) => (string) $string !== '',
                fn (Stringable $string) => new ImmutableString(" {$string}"),
            );

        $compiledChildren = sprintf(
            <<<'HTML'
            <%s%s>
            %s
            </%s>
            HTML,
            '%s',
            $attributes,
            $collectionElement->compile(),
            '%s',
        );

        $compiled = sprintf(
'<?php 
$vars = get_defined_vars();
unset($vars[\'_view\'], $vars[\'_path\'], $vars[\'_data\'], $vars[\'_propIsLocal\'], $vars[\'_isIsLocal\'], $vars[\'_previousAttributes\'], $vars[\'_previousSlots\'], $vars[\'slots\']);

echo \Tempest\get(\Tempest\View\Renderers\TempestViewRenderer::class)->render(\Tempest\view(sprintf(<<<\'HTML\'
%s
HTML, %s, %s), ...$vars)); ?>
',
            $compiledChildren,
            $isExpression ? $name : "'{$name}'",
            $isExpression ? $name : "'{$name}'",
        );

        return $compiled;
    }
}
