<?php
/**
 * @var string|null $action
 * @var string|Method|null $method
 * @var string|null $enctype
 * @var string|null $bag
 */

use Tempest\Http\Method;

$action ??= null;
$method ??= Method::POST;
$bag ??= null;

if ($method instanceof Method) {
    $method = $method->value;
}
?>

<form :action="$action" :method="$method" :enctype="$enctype">
    <x-csrf-token />
    
    <?php if ($bag !== null): ?>
        <input type="hidden" name="__error_bag" value="{{ $bag }}" />
    <?php endif; ?>

    <x-slot />
</form>
