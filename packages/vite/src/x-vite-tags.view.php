<?php
/**
 * @var array|null $entrypoints
 * @var string|null $entrypoint
 */

use Tempest\Vite\ViteConfig;

use function Tempest\get;
use function Tempest\vite_tags;

$viteConfig = get(ViteConfig::class);

$html = vite_tags($entrypoints ?? $entrypoint ?? $viteConfig->entrypoints);
?>

{!! $html !!}
