<?php
/**
 * @var string $is
 * @var \Tempest\Support\Arr\ImmutableArray $attributes
 */

use Tempest\View\Renderers\TempestViewRenderer;
use Tempest\View\Slot;

use function Tempest\Container\get;
use function Tempest\View\view;

$attributeString = $attributes
    ->map(fn (string $value, string $key) => "{$key}=\"{$value}\"")
    ->implode(' ');

$content = $slots[Slot::DEFAULT]->content ?? '';

$template = sprintf(<<<'HTML'
<%s %s>
%s
</%s>
HTML, $is, $attributeString, $content, $is);

$data = $scopedVariables ?? $_data ?? [];
$data = is_array($data) ? $data : [];
$html = get(TempestViewRenderer::class)->render(view($template, ...$data));
?>

{!! $html !!}
