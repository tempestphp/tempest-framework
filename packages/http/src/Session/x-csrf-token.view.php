<?php

use Tempest\Http\Session\Session;

use function Tempest\get;

$name = Session::CSRF_TOKEN_KEY;
$token = get(Session::class)->token;
?>

<input type="hidden" name="{{ $name }}" value="{{ $token }}" />
