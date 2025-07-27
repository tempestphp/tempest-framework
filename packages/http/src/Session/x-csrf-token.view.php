<?php

use Tempest\Http\Session\Session;

use function Tempest\Http\csrf_token;

$name = Session::CSRF_TOKEN_KEY;
$value = csrf_token();
?>

<input type="hidden" :name="$name" :value="$value">
