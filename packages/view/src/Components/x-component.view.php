<?php
/**
 * @var string $is
 * @var \Tempest\Support\Arr\ImmutableArray $attributes
 */

use Tempest\View\Renderers\TempestViewRenderer;
use Tempest\View\Slot;

use function Tempest\Container\get;
use function Tempest\View\view;

$htmlAttributes = [];
$dataVariables = [];

foreach ($attributes as $key => $value) {
    if (is_object($value) || is_array($value)) {
        $dataVariables[$key] = $value;
    } elseif ($value === true) {
        $htmlAttributes[] = $key;
    } elseif ($value !== false && $value !== null) {
        $htmlAttributes[] = "{$key}=\"{$value}\"";
    }
}

$attributeString = implode(' ', $htmlAttributes);

$content = $slots[Slot::DEFAULT]->content ?? '';

$template = sprintf(<<<'HTML'
<%s %s>
%s
</%s>
HTML, $is, $attributeString, $content, $is);

$data = $scopedVariables ?? $_data ?? [];
$data = is_array($data) ? $data : [];
$data = array_merge($data, $dataVariables);
$html = get(TempestViewRenderer::class)->render(view($template, ...$data));
?>

{!! $html !!}
