<?php
$componentClass = 'component-class';
$componentStyle = 'display: block;';
?><x-fallthrough-test class="component-class" />
<x-fallthrough-test :class="$componentClass" />
<x-fallthrough-dynamic-test c="component-class" s="display: block;" />
<x-fallthrough-dynamic-test :c="$componentClass" :s="$componentStyle"/>
