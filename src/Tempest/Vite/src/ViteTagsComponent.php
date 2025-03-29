<?php

declare(strict_types=1);

namespace Tempest\Vite;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;

final readonly class ViteTagsComponent implements ViewComponent
{
    public function __construct(
        private ViteConfig $viteConfig,
    ) {}

    public static function getName(): string
    {
        return 'x-vite-tags';
    }

    public function compile(ViewComponentElement $element): string
    {
        $entrypoints = match (true) {
            $element->hasAttribute('entrypoints') => '$entrypoints',
            $element->hasAttribute('entrypoint') => '$entrypoint',
            default => var_export($this->viteConfig->entrypoints, return: true), // @mago-expect best-practices/no-debug-symbols
        };

        $config = match (true) {
            $element->hasAttribute('config') => '$config',
            default => 'null',
        };

        return <<<HTML
            <?= \Tempest\\vite_tags({$entrypoints}, tag: {$config}) ?>
        HTML;
    }
}
