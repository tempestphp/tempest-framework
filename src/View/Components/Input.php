<?php

declare(strict_types=1);

namespace Tempest\View\Components;

use Tempest\View\ViewComponent;

final readonly class Input implements ViewComponent
{
    public function __construct(
        private string $name,
        private string $label,
        private string $type = 'text',
    ) {
    }

    public static function getName(): string
    {
        return 'x-input';
    }

    public function render(string $slot): string
    {
        return <<<HTML
<div>
    <label for="{$this->name}">{$this->label}</label>
    <input type="{$this->type}" name="{$this->name}" id="{$this->name}" />
</div>
HTML;
    }
}
