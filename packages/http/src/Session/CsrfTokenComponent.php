<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;

final readonly class CsrfTokenComponent implements ViewComponent
{
    public static function getName(): string
    {
        return 'x-csrf-token';
    }

    public function compile(ViewComponentElement $element): string
    {
        $name = Session::CSRF_TOKEN_KEY;

        return <<<HTML
        <input type="hidden" name="{$name}" value="<?= \Tempest\\Http\\csrf_token() ?>">
        HTML;
    }
}
