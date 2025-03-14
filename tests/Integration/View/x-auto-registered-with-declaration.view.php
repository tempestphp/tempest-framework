<?php
use function Tempest\Support\Str\to_title_case;

?>

<x-component name="x-auto-registered-with-declaration">
	<span>{{ to_title_case('hello world') }}</span>
</x-component>
