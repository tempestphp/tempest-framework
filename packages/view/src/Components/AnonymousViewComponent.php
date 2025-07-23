<?php

declare(strict_types=1);

namespace Tempest\View\Components;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewComponentMetadata;
use Tempest\View\ViewComponentParameters;

final readonly class AnonymousViewComponent implements ViewComponent, ViewComponentMetadata
{
    public function __construct(
        public string $contents,
        public string $file,
    ) {}

    public static function getName(): string
    {
        return 'x-component';
    }

    public static function getParameters(): ViewComponentParameters
    {
        return new ViewComponentParameters();
    }

    public static function getDescription(): string
    {
        return 'A component that renders a view file.';
    }

    public static function hasSlots(): null
    {
        return null;
    }

    public static function getNamedSlots(): null
    {
        return null;
    }

    public static function getDeprecationMessage(): ?string
    {
        return null;
    }

    public function compile(ViewComponentElement $element): string
    {
        return $this->contents;
    }
}
