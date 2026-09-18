<?php
/**
 * SEE: docs/1-essentials/02-views.md for usage instructions
 * @var string|null $name
 * @var string|null $class
 * @var string|null $style
 * @var string|null $width
 * @var string|null $height
 */

use Tempest\Core\Environment;
use Tempest\Icon\Icon;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Container\get;
use function Tempest\Support\str;

$name ??= null;
$environment = get(Environment::class);

$svg = str(is_string($name) ? get(Icon::class)->render($name) : null)
    ->when(
        static fn (ImmutableString $s): bool => $s->toString() === '' && $environment->isLocal(),
        static fn (ImmutableString $s): ImmutableString => str("<!-- unknown-icon: {$name} -->"),
    )
    ->replace(
        search: ' width="1em" height="1em"',
        replace: '',
    )
    ->when(
        $style ?? null,
        static fn (ImmutableString $s): ImmutableString => $s->replace(
            search: '<svg',
            replace: "<svg style=\"{$style}\"",
        ),
    )
    ->when(
        $class ?? null,
        static fn (ImmutableString $s): ImmutableString => $s->replace(
            search: '<svg',
            replace: "<svg class=\"{$class}\"",
        ),
    )
    ->when(
        isset($width, $height),
        static fn (ImmutableString $s): ImmutableString => $s
            ->replace(
                search: '<svg',
                replace: "<svg width=\"{$width}\" height=\"{$height}\"",
            ),
    )
    ->when(
        ! isset($width, $height) && ! isset($style) && ! isset($class),
        static fn (ImmutableString $s): ImmutableString => $s
            ->replace(
                search: '<svg',
                replace: '<svg width="1em" height="1em"',
            ),
    )
    ->toString();
?>

{!! $svg !!}
