<?php

declare(strict_types=1);

namespace Tempest\View\Components;

use Tempest\Validation\Rule;
use Tempest\View\View;
use Tempest\View\ViewComponent;
use Tempest\View\ViewRenderer;

final readonly class Input implements ViewComponent
{
    public function __construct(
        private View $view,
        private string $name,
        private string $label,
        private ?string $default = null,
        private string $type = 'text',
    ) {
    }

    public static function getName(): string
    {
        return 'x-input';
    }

    public function render(ViewRenderer $renderer): string
    {
        $errors = $this->view->getErrorsFor($this->name);

        $errorHtml = '';

        if ($errors) {
            $errorHtml = '<div>' . implode('', array_map(
                fn (Rule $failingRule) => "<div>{$failingRule->message()}</div>",
                $errors
            )) . '</div>';
        }

        return <<<HTML
<div>
    <label for="{$this->name}">{$this->label}</label>
    <input type="{$this->type}" name="{$this->name}" id="{$this->name}" value="{$this->view->original($this->name, $this->default)}" />
    {$errorHtml}
</div>
HTML;
    }
}
