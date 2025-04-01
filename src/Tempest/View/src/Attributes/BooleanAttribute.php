<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Exception;
use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\GenericElement;

final readonly class BooleanAttribute implements Attribute
{
    public function __construct(
        private string $attributeName,
    ) {}

    public function apply(Element $element): Element
    {
        if (! ($element instanceof GenericElement)) {
            throw new Exception('This cannot happen');
        }

        $element
            ->addRawAttribute(sprintf(
                '<?php if(%s) { ?>%s<?php } ?>',
                $element->getAttribute($this->attributeName),
                ltrim($this->attributeName, ':'),
            ))
            ->unsetAttribute($this->attributeName);

        return $element;
    }

    public static function matches(Element $element, string $attributeName): bool
    {
        if (! ($element instanceof GenericElement)) {
            return false;
        }

        if (! str_starts_with($attributeName, ':')) {
            return false;
        }

        $attributeName = ltrim($attributeName, ':');

        $allowedElements = match ($attributeName) {
            'autofocus' => true,
            'allowfullscreen' => ['iframe'],
            'alpha', 'checked' => ['input'],
            'async', 'nomodule', 'defer' => ['script'],
            'autoplay', 'controls', 'loop', 'muted' => ['audio', 'video'],
            'default' => ['track'],
            'disabled' => ['link', 'fieldset', 'button', 'input', 'optgroup', 'option', 'select', 'textarea;'],
            'formnovalidate' => ['button', 'input'],
            'inert', 'itemscope' => ['HTML elements'],
            'ismap' => ['img'],
            'multiple' => ['input', 'select'],
            'open' => ['dialog', 'details'],
            'playsinline' => ['video'],
            'readonly' => ['input', 'textarea'],
            'required' => ['input', 'select', 'textarea'],
            'reversed' => ['ol'],
            'selected' => ['option'],
            'shadowrootclonable', 'shadowrootserializable', 'shadowrootdelegatesfocus' => ['template'],
            default => null,
        };

        return match (true) {
            $allowedElements === null => false,
            $allowedElements === true => true,
            default => in_array($element->getTag(), $allowedElements, strict: true),
        };
    }
}
