<?php

declare(strict_types=1);

namespace Tempest\Vite;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;

final class ViteTagsComponent implements ViewComponent
{
    public function __construct(private readonly ViteConfig $viteConfig)
    {
    }

    public static function getName(): string
    {
        return 'x-vite-tags';
    }

    public function compile(ViewComponentElement $element): string
    {
        $entrypoints = $element->hasAttribute('entrypoints') || $element->hasAttribute('entrypoint')
            ? '$entrypoint ?? $entrypoint'
            : var_export($this->viteConfig->build->entrypoints, return: true);

        return <<<HTML
                <?= \Tempest\\vite_tags({$entrypoints}) ?>
            HTML;
    }
}
