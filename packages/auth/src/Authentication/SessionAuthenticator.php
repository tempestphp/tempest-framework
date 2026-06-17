<?php

declare(strict_types=1);

namespace Tempest\Auth\Authentication;

use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionManager;

final class SessionAuthenticator implements Authenticator
{
    public const string AUTHENTICATABLE_KEY = '#authenticatable:id';

    public const string AUTHENTICATABLE_CLASS = '#authenticatable:class';

    private int|string|null $currentId = null;

    private ?string $currentClass = null;

    private ?Authenticatable $current = null;

    public function __construct(
        private readonly SessionManager $sessionManager,
        private readonly Session $session,
        private readonly AuthenticatableResolver $authenticatableResolver,
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
    }

    public function deauthenticate(): void
    {
        $this->session->remove(self::AUTHENTICATABLE_KEY);
        $this->session->remove(self::AUTHENTICATABLE_CLASS);
        $this->clearCurrent();

        $this->sessionManager->save($this->session);
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
