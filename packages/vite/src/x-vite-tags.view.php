<?php
/**
 * @var array|null $entrypoints
 * @var string|null $entrypoint
 */

use Tempest\Support\Html\HtmlString;
use Tempest\Vite;
use Tempest\Vite\ViteConfig;

use function Tempest\Container\get;

$viteConfig = get(ViteConfig::class);
$tags = Vite\get_tags($entrypoints ?? $entrypoint ?? $viteConfig->entrypoints);
$html = new HtmlString(implode('', $tags));
?>

{!! $html !!}
