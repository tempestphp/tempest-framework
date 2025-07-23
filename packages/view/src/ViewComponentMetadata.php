<?php

declare(strict_types=1);

namespace Tempest\View;

interface ViewComponentMetadata
{
    public static function getParameters(): ViewComponentParameters;

    public static function getDescription(): ?string;

    /**
     * Returns `true` if the component has slots, `false` if it does not, or `null` if it is unknown.
     */
    public static function hasSlots(): ?bool;

    /**
     * Returns an `array` of named slots, or `null` if it's unknown whether the component has named slots.
     */
    public static function getNamedSlots(): ?array;

    /**
     * Returns the deprecation message for the component, or `null` if it is not deprecated.
     */
    public static function getDeprecationMessage(): ?string;
}
