<?php

use Tempest\Http\Session\Session;

use function Tempest\get;

?>

<input type="hidden" name="{{ Session::CSRF_TOKEN_KEY }}" value="{{ get(Session::class)->token }}" />
