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
?>

<form :action="$action" :method="$method" :enctype="$enctype">
    <x-slot />
</form>
