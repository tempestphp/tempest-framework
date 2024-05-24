<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Database\Query;
use Tempest\Http\Session\Session;
use function Tempest\make;

final class DatabaseAuthenticator implements Authenticator
{
    use AuthenticatorWithSession;

    public function __construct(
        protected AuthConfig $config,
        Session $session,
    ) {
        $this->session = $session;
    }

    public function login(Identifiable $identifiable): void
    {
        $this->createSession($identifiable);
    }

    public function logout(): void
    {
        $this->destroySession();
    }

    public function user(): Identifiable|array|null
    {
        // TODO: redo implementation
        //        $sessionUser = $this->session->get(self::SESSION_USER_KEY);
        //        if (is_null($sessionUser)) {
        //            return null;
        //        }
        //
        //        $query = new Query(
        //            "SELECT * FROM :table WHERE :identifier_field = :identifier_value LIMIT 1",
        //            [
        //                'table' => $sessionUser['source'],
        //                'identifier_field' => $sessionUser['identifier_field'],
        //                'identifier_value' => $sessionUser['identifier_value'],
        //            ]
        //        );
        //
        //        return is_null($this->config->authenticable)
        //            ? $query->fetchFirst()
        //            : make($this->config->authenticable)->from($query->fetchFirst());
    }
}
