<?php

/**
 * @var string|null $content The markdown content from a variable
 */

use Tempest\Markdown\Markdown;
use Tempest\View\Slot;

use function Tempest\Container\get;

$content ??= $slots[Slot::DEFAULT]->content ?? '';
$markdown = get(Markdown::class);
$parsed = $markdown->parse($content)->html;
?>

{!! $parsed !!}
