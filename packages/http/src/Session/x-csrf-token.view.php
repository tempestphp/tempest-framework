<?php

use Tempest\Http\Session\Session;

use function Tempest\get;

$csrfFieldName = Session::CSRF_TOKEN_KEY;
$csrfTokenValue = get(Session::class)->token;
?>

<input type="hidden" name="{{ $csrfFieldName }}" value="{{ $csrfTokenValue }}" />
