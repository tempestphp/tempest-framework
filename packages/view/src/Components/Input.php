<?php

declare(strict_types=1);

namespace Tempest\View\Components;

use Tempest\Http\Session\Session;
use Tempest\Validation\Rule;
use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewComponentParameter;
use Tempest\View\ViewComponentParameters;

final readonly class Input implements ViewComponent
{
    public function __construct(
        private Session $session,
    ) {}

    public static function getName(): string
    {
        return 'x-input';
    }

    public static function getParameters(): ViewComponentParameters
    {
        return new ViewComponentParameters(
            new ViewComponentParameter(
                name: 'name',
                description: 'The name of the input field.',
            ),
            new ViewComponentParameter(
                name: 'label',
                description: 'The label for the input field.',
            ),
            new ViewComponentParameter(
                name: 'type',
                description: 'The type of the input field (e.g., text, email, password, textarea).',
            ),
            new ViewComponentParameter(
                name: 'default',
                description: 'The default value for the input field.',
            ),
        );
    }

    public function compile(ViewComponentElement $element): string
    {
        $name = $element->getAttribute('name');
        $label = $element->getAttribute('label');
        $type = $element->getAttribute('type');
        $default = $element->getAttribute('default');

        $errors = $this->getErrorsFor($name);

        $errorHtml = '';

        if ($errors) {
            $errorHtml = '<div>' . implode('', array_map(
                fn (Rule $failingRule) => "<div>{$failingRule->message()}</div>",
                $errors,
            )) . '</div>';
        }

        if ($type === 'textarea') {
            return <<<HTML
            <div>
                <label for="{$name}">{$label}</label>
                <textarea name="{$name}" id="{$name}">{$this->original($name, $default)}</textarea>
                {$errorHtml}
            </div>
            HTML;
        }

        return <<<HTML
        <div>
            <label for="{$name}">{$label}</label>
            <input type="{$type}" name="{$name}" id="{$name}" value="{$this->original($name, $default)}" />
            {$errorHtml}
        </div>
        HTML;
    }

    public function original(string $name, mixed $default = ''): mixed
    {
        return $this->session->get(Session::ORIGINAL_VALUES)[$name] ?? $default;
    }

    /** @return \Tempest\Validation\Rule[] */
    public function getErrorsFor(string $name): array
    {
        return $this->session->get(Session::VALIDATION_ERRORS)[$name] ?? [];
    }
}
