<?php

use Tempest\Http\Session\Session;

use function Tempest\Container\get;

// The flash value is consumed while the view renders, i.e. after the session
// middleware has run. It must still expire for the following request.
$message = get(Session::class)->get('message');
?>

<div id="flash">{{ $message }}</div>
