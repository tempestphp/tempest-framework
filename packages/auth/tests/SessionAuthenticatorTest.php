<?php

declare(strict_types=1);

namespace Tempest\Auth\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Auth\Authentication\Authenticatable;
use Tempest\Auth\Authentication\AuthenticatableResolver;
use Tempest\Auth\Authentication\SessionAuthenticator;
use Tempest\Auth\Authentication\SessionAuthenticatorReset;
use Tempest\DateTime\DateTime;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;

final class SessionAuthenticatorTest extends TestCase
{
    #[Test]
    public function current_memoizes_the_resolved_authenticatable_for_the_current_session_identity(): void
    {
        $authenticatable = new MemoizedAuthenticatable(id: 1);
        $resolver = new CountingAuthenticatableResolver($authenticatable);
        $session = $this->createSession();
        $session->set(SessionAuthenticator::AUTHENTICATABLE_KEY, 1);
        $session->set(SessionAuthenticator::AUTHENTICATABLE_CLASS, MemoizedAuthenticatable::class);

        $authenticator = new SessionAuthenticator(
            sessionManager: new TestingSessionManager(),
            session: $session,
            authenticatableResolver: $resolver,
        );

        $this->assertSame($authenticatable, $authenticator->current());
        $this->assertSame($authenticatable, $authenticator->current());
        $this->assertSame(1, $resolver->resolveCalls);
    }

    #[Test]
    public function current_memoizes_a_missing_authenticatable_for_the_current_session_identity(): void
    {
        $resolver = new CountingAuthenticatableResolver();
        $session = $this->createSession();
        $session->set(SessionAuthenticator::AUTHENTICATABLE_KEY, 1);
        $session->set(SessionAuthenticator::AUTHENTICATABLE_CLASS, MemoizedAuthenticatable::class);

        $authenticator = new SessionAuthenticator(
            sessionManager: new TestingSessionManager(),
            session: $session,
            authenticatableResolver: $resolver,
        );

        $this->assertNull($authenticator->current());
        $this->assertNull($authenticator->current());
        $this->assertSame(1, $resolver->resolveCalls);
    }

    #[Test]
    public function current_re_resolves_when_the_session_identity_changes(): void
    {
        $resolver = new CountingAuthenticatableResolver(
            new MemoizedAuthenticatable(id: 1),
            new MemoizedAuthenticatable(id: 2),
        );
        $session = $this->createSession();
        $session->set(SessionAuthenticator::AUTHENTICATABLE_KEY, 1);
        $session->set(SessionAuthenticator::AUTHENTICATABLE_CLASS, MemoizedAuthenticatable::class);

        $authenticator = new SessionAuthenticator(
            sessionManager: new TestingSessionManager(),
            session: $session,
            authenticatableResolver: $resolver,
        );

        $current = $authenticator->current();
        $this->assertInstanceOf(MemoizedAuthenticatable::class, $current);
        $this->assertSame(1, $current->id);

        $session->set(SessionAuthenticator::AUTHENTICATABLE_KEY, 2);

        $current = $authenticator->current();
        $this->assertInstanceOf(MemoizedAuthenticatable::class, $current);
        $this->assertSame(2, $current->id);
        $this->assertSame(2, $resolver->resolveCalls);
    }

    #[Test]
    public function reset_clears_the_cached_current_authenticatable(): void
    {
        $authenticatable = new MemoizedAuthenticatable(id: 1);
        $resolver = new CountingAuthenticatableResolver($authenticatable);
        $session = $this->createSession();
        $session->set(SessionAuthenticator::AUTHENTICATABLE_KEY, 1);
        $session->set(SessionAuthenticator::AUTHENTICATABLE_CLASS, MemoizedAuthenticatable::class);

        $authenticator = new SessionAuthenticator(
            sessionManager: new TestingSessionManager(),
            session: $session,
            authenticatableResolver: $resolver,
        );

        $this->assertSame($authenticatable, $authenticator->current());

        new SessionAuthenticatorReset($authenticator)->reset();

        $this->assertSame($authenticatable, $authenticator->current());
        $this->assertSame(2, $resolver->resolveCalls);
    }

    #[Test]
    public function authenticate_replaces_a_cached_current_authenticatable(): void
    {
        $resolver = new CountingAuthenticatableResolver(
            new MemoizedAuthenticatable(id: 1),
            new MemoizedAuthenticatable(id: 2),
        );
        $session = $this->createSession();
        $session->set(SessionAuthenticator::AUTHENTICATABLE_KEY, 1);
        $session->set(SessionAuthenticator::AUTHENTICATABLE_CLASS, MemoizedAuthenticatable::class);

        $authenticator = new SessionAuthenticator(
            sessionManager: new TestingSessionManager(),
            session: $session,
            authenticatableResolver: $resolver,
        );

        $current = $authenticator->current();
        $this->assertInstanceOf(MemoizedAuthenticatable::class, $current);
        $this->assertSame(1, $current->id);

        $authenticator->authenticate(new MemoizedAuthenticatable(id: 2));

        $current = $authenticator->current();
        $this->assertInstanceOf(MemoizedAuthenticatable::class, $current);
        $this->assertSame(2, $current->id);
    }

    private function createSession(): Session
    {
        $now = DateTime::now();

        return new Session(
            id: new SessionId('test-session'),
            createdAt: $now,
            lastActiveAt: $now,
        );
    }
}

final readonly class MemoizedAuthenticatable implements Authenticatable
{
    public function __construct(
        public int $id,
    ) {}
}

final class CountingAuthenticatableResolver implements AuthenticatableResolver
{
    public int $resolveCalls = 0;

    /** @var array<int|string, MemoizedAuthenticatable> */
    private array $authenticatables = [];

    public function __construct(MemoizedAuthenticatable ...$authenticatables)
    {
        foreach ($authenticatables as $authenticatable) {
            $this->authenticatables[$authenticatable->id] = $authenticatable;
        }
    }

    public function resolve(int|string $id, string $class): ?Authenticatable
    {
        $this->resolveCalls++;

        if ($class !== MemoizedAuthenticatable::class) {
            return null;
        }

        return $this->authenticatables[$id] ?? null;
    }

    public function resolveId(Authenticatable $authenticatable): int
    {
        return $authenticatable instanceof MemoizedAuthenticatable ? $authenticatable->id : 0;
    }
}

final class TestingSessionManager implements SessionManager
{
    public int $savedSessions = 0;

    public function getOrCreate(SessionId $id): Session
    {
        $now = DateTime::now();

        return new Session($id, $now, $now);
    }

    public function save(Session $session): void
    {
        $this->savedSessions++;
    }

    public function delete(Session $session): void {}

    public function isValid(Session $session): bool
    {
        return true;
    }

    public function deleteExpiredSessions(): void {}
}
