<?php

namespace Tempest\View\Components;

use Stringable;
use Tempest\Support\Str\ImmutableString;
use Tempest\View\Elements\CollectionElement;
use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\Parser\Token;
use Tempest\View\Renderers\TempestViewRenderer;
use Tempest\View\ViewComponent;

use function Tempest\Support\arr;

final class DynamicViewComponent implements ViewComponent
{
    public function __construct(
        private Token $token,
    ) {}

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
            ->filter(fn (string $_value, string $key) => $key !== 'is' && $key !== ':is')
            ->map(function (string $value, string $key) {
                return sprintf('%s="%s"', $key, trim($value));
            })
            ->implode(' ')
            ->when(
                fn (Stringable $string) => ((string) $string) !== '',
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

        return sprintf(
            '<?php 
echo \Tempest\get(%s::class)->render(\Tempest\view(sprintf(<<<\'HTML\'
%s
HTML, %s, %s), ...$this->data)); ?>
',
            TempestViewRenderer::class,
            $compiledChildren,
            $isExpression ? '$is' : "'{$name}'",
            $isExpression ? '$is' : "'{$name}'",
        );
    }
}
