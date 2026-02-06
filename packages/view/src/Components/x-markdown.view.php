<?php

/**
 * @var string|null $content The markdown content from a variable
 */

use League\CommonMark\MarkdownConverter;
use Tempest\View\Slot;

use function Tempest\Container\get;

$content ??= $slots[Slot::DEFAULT]->content ?? '';
$markdown = get(MarkdownConverter::class);
$parsed = $markdown->convert($content)->getContent();
?>

{!! $parsed !!}
