<?php
$foo ??= null;
$bar ??= null;
?>

<div :if="$foo && $bar" :foo="$foo" :bar="$bar"><x-slot /></div>
<div :else><x-slot /></div>
