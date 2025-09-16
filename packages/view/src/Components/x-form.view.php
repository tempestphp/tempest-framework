<?php
/**
 * @var string|null $action
 * @var string|Method|null $method
 * @var string|null $enctype
 */

use Tempest\Http\Method;

$action ??= null;
$method ??= Method::POST;

if ($method instanceof Method) {
    $method = $method->value;
}

$needsSpoofing = Method::trySpoofingFrom($method) !== null;
$formMethod = $needsSpoofing ? 'POST' : $method;
?>

<form :action="$action" :method="$formMethod" :enctype="$enctype">
    <x-csrf-token />

    <?php if ($needsSpoofing): ?>
        <input type="hidden" name="_method" value="<?= htmlspecialchars($method) ?>">
    <?php endif; ?>

    <x-slot />
</form>
