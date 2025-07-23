<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewComponentMetadata;
use Tempest\View\ViewComponentParameters;

final readonly class BaseLayoutComponent implements ViewComponent, ViewComponentMetadata
{
    public static function getName(): string
    {
        return 'x-base-layout';
    }

    public static function getParameters(): ViewComponentParameters
    {
        return new ViewComponentParameters();
    }

    public static function getDescription(): string
    {
        return 'A test component with slots.';
    }

    public static function hasSlots(): bool
    {
        return true;
    }

    public static function getNamedSlots(): array
    {
        return [];
    }

    public static function getDeprecationMessage(): ?string
    {
        return null;
    }

    public function compile(ViewComponentElement $element): string
    {
        return <<<HTML
        <div class="base">
            <x-slot />
        </div>
        HTML;
    }
}
