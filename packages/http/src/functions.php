<?php

namespace Tempest\Http;

use Tempest;
use Tempest\Http\Session\Session;

/**
 * Gets the session token used for cross-site request forgery protection.
 */
function csrf_token(): string
{
    return Tempest\get(Session::class)->token;
}
