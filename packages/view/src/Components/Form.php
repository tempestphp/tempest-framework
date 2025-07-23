<?php

declare(strict_types=1);

namespace Tempest\View\Components;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewComponentParameter;
use Tempest\View\ViewComponentParameters;

final readonly class Form implements ViewComponent
{
    public static function getName(): string
    {
        return 'x-form';
    }

    public static function getParameters(): ViewComponentParameters
    {
        return new ViewComponentParameters(
            new ViewComponentParameter(
                name: 'action',
                description: 'The URL to which the form will be submitted.',
            ),
            new ViewComponentParameter(
                name: 'method',
                description: 'The HTTP method to use when submitting the form.',
                default: 'post',
            ),
            new ViewComponentParameter(
                name: 'enctype',
                description: 'The encoding type for the form data.',
                default: '',
            ),
        );
    }

    public function compile(ViewComponentElement $element): string
    {
        $action = $element->getAttribute('action');
        $method = $element->getAttribute('method') ?? 'post';
        $enctype = $element->hasAttribute('enctype') ? sprintf('enctype="%s"', $element->getAttribute('enctype')) : '';

        return <<<HTML
        <form action="{$action}" method="{$method}" {$enctype}>
            <x-slot />
        </form>
        HTML;
    }
}
