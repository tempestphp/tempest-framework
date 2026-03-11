<?php
$componentClass = 'component-class';
$componentStyle = 'display: block;';
?><x-fallthrough-preamble-test class="component-class" />
<x-fallthrough-preamble-test :class="$componentClass" />
<x-fallthrough-preamble-dynamic-test c="component-class" s="display: block;" />
<x-fallthrough-preamble-dynamic-test :c="$componentClass" :s="$componentStyle"/>
