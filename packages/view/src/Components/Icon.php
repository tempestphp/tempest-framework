<?php

declare(strict_types=1);

namespace Tempest\View\Components;

use Tempest\Core\AppConfig;
use Tempest\Core\Environment;
use Tempest\Icon\Icon as Iconify;
use Tempest\Support\Html\HtmlString;
use Tempest\Support\Str\ImmutableString;
use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;

final readonly class Icon implements ViewComponent
{
    public function __construct(
        private AppConfig $appConfig,
        private Iconify $iconify,
    ) {}

    public static function getName(): string
    {
        return 'x-icon';
    }

    public function compile(ViewComponentElement $element): string
    {
        if (! $element->hasAttribute('name')) {
            throw new \InvalidArgumentException('The `name` attribute is required for the `x-icon` component.');
        }

        return sprintf("<?= \Tempest\\get(%s::class)->render(\$name, \$class ?? null) ?>", static::class);
    }

    public function render(string $name, ?string $class = null): HtmlString
    {
        $html = $this->iconify->render($name);

        return new HtmlString(match (true) {
            is_null($html) => match ($this->appConfig->environment) {
                Environment::LOCAL => '<!-- unknown-icon: ' . $name . ' -->',
                default => '',
            },
            is_string($class) => $this->injectClass($html, $class),
            default => $html,
        });
    }

    /**
     * Forwards the user-provided class attribute to the SVG element
     */
    private function injectClass(string $svg, string $class): string
    {
        return new ImmutableString($svg)
            ->replace(
                search: '<svg',
                replace: "<svg class=\"{$class}\"",
            )
            ->toString();
    }
}
