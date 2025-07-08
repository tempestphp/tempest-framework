<?php

use Tempest\View\Components\InputError;

use function Tempest\get;

$inputError = get(InputError::class);
$errors = $inputError->getErrorsFor($name ?? null);
?>

<div :if="$errors !== null" :class="$class ?? ''">
    <div :foreach="$errors as $error">
        {{ $error->message() }}
    </div>
</div>
