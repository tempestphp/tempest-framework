<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewComponentMetadata;
use Tempest\View\ViewComponentParameters;

final readonly class CsrfTokenComponent implements ViewComponent, ViewComponentMetadata
{
    public static function getName(): string
    {
        return 'x-csrf-token';
    }

    public static function getParameters(): ViewComponentParameters
    {
        return new ViewComponentParameters();
    }

    public static function getDescription(): string
    {
        return 'A component that generates a CSRF token input field for forms.';
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

    public function compile(ViewComponentElement $element): string
    {
        $name = Session::CSRF_TOKEN_KEY;

        return <<<HTML
        <input type="hidden" name="{$name}" value="<?= \Tempest\\Http\\csrf_token() ?>">
        HTML;
    }
}
