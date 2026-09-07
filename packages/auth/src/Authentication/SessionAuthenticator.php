<?php

declare(strict_types=1);

namespace Tempest\Auth\Authentication;

use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionRegenerator;

final class SessionAuthenticator implements Authenticator
{
    public const string AUTHENTICATABLE_KEY = '#authenticatable:id';

    public const string AUTHENTICATABLE_CLASS = '#authenticatable:class';

    private int|string|null $currentId = null;

    private ?string $currentClass = null;

    private ?Authenticatable $current = null;

    public function __construct(
        private readonly Session $session,
        private readonly AuthenticatableResolver $authenticatableResolver,
        private readonly SessionRegenerator $sessionRegenerator,
    ) {}

    public function authenticate(Authenticatable $authenticatable): void
    {
        $id = $this->authenticatableResolver->resolveId($authenticatable);
        $class = $authenticatable::class;

        $this->session->set(
            key: self::AUTHENTICATABLE_CLASS,
            value: $class,
        );

        $this->session->set(
            key: self::AUTHENTICATABLE_KEY,
            value: $id,
        );

        $this->currentId = $id;
        $this->currentClass = $class;
        $this->current = $authenticatable;

        // The session identifier must not survive a change in privilege level, or one
        // known to an attacker before authentication stays valid afterwards.
        $this->sessionRegenerator->regenerate();
    }

    public function deauthenticate(): void
    {
        $this->clearCurrent();

        // Regenerate session without preserving data to prevent session fixation
        // and purge all authenticated user data.
        $this->sessionRegenerator->invalidate();
    }

    public function current(): ?Authenticatable
    {
        $id = $this->session->get(self::AUTHENTICATABLE_KEY);
        $class = $this->session->get(self::AUTHENTICATABLE_CLASS);

        if (! $id || ! $class) {
            $this->clearCurrent();

            return null;
        }

        if ($this->currentId === $id && $this->currentClass === $class) {
            return $this->current;
        }

        $this->currentId = $id;
        $this->currentClass = $class;
        $this->current = $this->authenticatableResolver->resolve($id, $class);

        return $this->current;
    }

    public function clearCurrent(): void
    {
        $this->currentId = null;
        $this->currentClass = null;
        $this->current = null;
    }
}
