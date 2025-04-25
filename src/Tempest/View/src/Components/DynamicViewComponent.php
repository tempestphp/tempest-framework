<?php

namespace Tempest\View\Components;

use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\View\Elements\ElementFactory;
use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\GenericView;
use Tempest\View\Parser\TempestViewCompiler;
use Tempest\View\Parser\Token;
use Tempest\View\ViewComponent;
use Tempest\View\ViewConfig;
use Tempest\View\ViewRenderer;

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

        return sprintf(
            '<?php eval(\'?>\' . \Tempest\get(%s::class)->render(%s, %s)); ?>',
            self::class,
            $isExpression ? $name : "'{$name}'",
            var_export($element->getAttributes(), true), // @mago-expect best-practices/no-debug-symbols
        );
    }

    public function render(string $name, array $attributes): string
    {
        $viewComponent = $this->viewConfig->viewComponents[$name] ?? null;

        $element = new ViewComponentElement(
            environment: $this->appConfig->environment,
            compiler: $this->compiler,
            viewComponent: $viewComponent,
            attributes: $attributes,
        );

        return $element->compile();
    }
}
