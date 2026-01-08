<?php
/**
 * @var string $name
 * @var string|null $label
 * @var string|null $id
 * @var string|null $type
 * @var string|null $default
 */

use Tempest\Http\Session\FormSession;
use Tempest\Validation\Validator;

use function Tempest\Container\get;
use function Tempest\Support\str;

/** @var FormSession $formSession */
$formSession = get(FormSession::class);

/** @var Validator $validator */
$validator = get(Validator::class);

$label ??= str($name)->title();
$id ??= $name;
$type ??= 'text';
$default ??= null;

$errors = $formSession->getErrorsFor($name);
$original = $formSession->getOriginalValueFor($name, $default);
?>

<div>
    <label :for="$id">{{ $label }}</label>

    <textarea :if="$type === 'textarea'" :name="$name" :id="$id">{{ $original }}</textarea>
    <input :else :type="$type" :name="$name" :id="$id" :value="$original"/>

    <ul :if="$errors !== []">
        <li :foreach="$errors as $error">
            {{ $validator->getErrorMessage($error) }}
        </li>
    </ul>
</div>
