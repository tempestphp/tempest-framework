<?php
/**
 * @var string $name
 * @var string|null $class
 */

use Tempest\Core\AppConfig;
use Tempest\Icon\Icon;
use function Tempest\get;
use function Tempest\Support\str;

$class ??= null;
$name ??= null;
$appConfig = get(AppConfig::class);

if ($name) {
    $svg = get(Icon::class)->render($name);
} else {
    $svg = null;
}

if ($svg === null && $appConfig->environment->isLocal()) {
    $svg = '<!-- unknown-icon: ' . $name . ' -->';
}

if ($class) {
    $svg = str($svg)
        ->replace(
            search: '<svg',
            replace: "<svg class=\"{$class}\"",
        )
        ->toString();
}
?>

{!! $svg !!}
