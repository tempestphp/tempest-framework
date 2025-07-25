<?php
/**
 * @var string $name
 * @var string|null $label
 * @var string|null $id
 * @var string|null $type
 * @var string|null $default
 */

use Tempest\Http\Session\Session;
use function Tempest\get;
use function Tempest\Support\str;

/** @var Session $session */
$session = get(Session::class);

$label ??= str($name)->title();
$id ??= $name;
$type ??= 'text';
$default ??= null;

$errors = $session->getErrorsFor($name);
$original = $session->getOriginalValueFor($name, $default);
?>

<div>
    <label :for="$id">{{ $label }}</label>

    <x-template :if="$type === 'textarea'">
        <textarea :name="$name" :id="$id">{{ $original }}</textarea>
    </x-template>
    <x-template :else>
        <input :type="$type" :name="$name" :id="$id" :value="$original"/>
    </x-template>

    <div :if="$errors !== []">
        <div :foreach="$errors as $error">
            {{ $error->message() }}
        </div>
    </div>
</div>

