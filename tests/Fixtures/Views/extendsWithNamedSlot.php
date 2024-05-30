<?php
declare(strict_types=1);
use Tempest\View\GenericView;

/** @var GenericView $this */
$this->extends('Views/baseWithNamedSlot.php');
?>

<h1>beginning</h1>

<x-slot name="namedSlot">
<h1>named slot</h1>
</x-slot>

<p>in between</p>

<x-slot name="namedSlot2">
<h1>named slot 2</h1>
</x-slot>

<p>default slot</p>
