<?php

namespace Tempest\Validation;

interface HasTranslationVariables
{
    /**
     * Returns variables used for translation validation error messages.
     *
     * @return array<string,mixed>
     */
    public function getTranslationVariables(): array;
}
