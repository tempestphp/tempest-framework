<?php

declare(strict_types=1);

namespace Tempest\Auth\Exceptions;

use Exception;

final class OAuthStateWasInvalid extends Exception implements AuthenticationException
{
}
