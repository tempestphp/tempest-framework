<?php

declare(strict_types=1);

namespace Tempest\Vite;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewComponentParameter;
use Tempest\View\ViewComponentParameters;

final readonly class ViteTagsComponent implements ViewComponent
{
    public function __construct(
        private ViteConfig $viteConfig,
    ) {}

    public static function getName(): string
    {
        return 'x-vite-tags';
    }

    public static function getParameters(): ViewComponentParameters
    {
        return new ViewComponentParameters(
            new ViewComponentParameter(
                name: 'entrypoints',
                description: 'The entrypoints to use for the Vite tags.',
            ),
            new ViewComponentParameter(
                name: 'entrypoint',
                description: 'The entrypoint to use for the Vite tags.',
            ),
        );
    }

    public function compile(ViewComponentElement $element): string
    {
        $entrypoints = match (true) {
            $element->hasAttribute('entrypoints') => '$entrypoints',
            $element->hasAttribute('entrypoint') => '$entrypoint',
            default => var_export($this->viteConfig->entrypoints, return: true), // @mago-expect best-practices/no-debug-symbols
        };

        return <<<HTML
            <?= \Tempest\\vite_tags({$entrypoints}) ?>
        HTML;
    }
}
