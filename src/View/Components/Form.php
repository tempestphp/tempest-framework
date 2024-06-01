<?php

declare(strict_types=1);

namespace Tempest\View\Components;

use Tempest\View\ViewComponent;

final readonly class Form implements ViewComponent
{
    public function __construct(
        private string $action,
        private string $method = 'post',
    ) {
    }

    public static function getName(): string
    {
        return 'x-form';
    }

    public function render(string $slot): string
    {
        return <<<HTML
<form action="{$this->action}" method="{$this->method}">
    {$slot}
</form>
HTML;
    }
}
