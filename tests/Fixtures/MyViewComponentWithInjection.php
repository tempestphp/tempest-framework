<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewComponentMetadata;
use Tempest\View\ViewComponentParameters;

final readonly class MyViewComponentWithInjection implements ViewComponent, ViewComponentMetadata
{
    public static function getName(): string
    {
        return 'x-with-injection';
    }

    public static function getParameters(): ViewComponentParameters
    {
        return new ViewComponentParameters();
    }

    public static function getDescription(): string
    {
        return 'A test component.';
    }

    public static function hasSlots(): bool
    {
        return false;
    }

    public static function getNamedSlots(): array
    {
        return [];
    }

    public static function getDeprecationMessage(): ?string
    {
        return null;
    }

    public function __construct() {}

    public function compile(ViewComponentElement $element): string
    {
        return 'hi';
    }
}
