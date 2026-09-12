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
use Tempest\Http\Session\SessionIdResolver;
use Tempest\Http\Session\SessionManager;
use Tempest\Http\Session\SessionRegenerator;

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
            sessionRegenerator: $this->createRegenerator($session),
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
            sessionRegenerator: $this->createRegenerator($session),
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
            sessionRegenerator: $this->createRegenerator($session),
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
            sessionRegenerator: $this->createRegenerator($session),
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
            sessionRegenerator: $this->createRegenerator($session),
        );

        $current = $authenticator->current();
        $this->assertInstanceOf(MemoizedAuthenticatable::class, $current);
        $this->assertSame(1, $current->id);

        $authenticator->authenticate(new MemoizedAuthenticatable(id: 2));

        $current = $authenticator->current();
        $this->assertInstanceOf(MemoizedAuthenticatable::class, $current);
        $this->assertSame(2, $current->id);
    }

    #[Test]
    public function authenticate_regenerates_the_session_identifier(): void
    {
        $session = $this->createSession();
        $sessionManager = new TestingSessionManager();

        $authenticator = new SessionAuthenticator(
            sessionManager: $sessionManager,
            session: $session,
            authenticatableResolver: new CountingAuthenticatableResolver(),
            sessionRegenerator: $this->createRegenerator($session, $sessionManager),
        );

        $authenticator->authenticate(new MemoizedAuthenticatable(id: 1));

        $this->assertNotSame('test-session', (string) $session->id);
        $this->assertSame(1, $sessionManager->deletedSessions);
        $this->assertSame(1, $sessionManager->savedSessions);
        $this->assertSame(1, $session->get(SessionAuthenticator::AUTHENTICATABLE_KEY));
    }

    #[Test]
    public function deauthenticate_regenerates_the_session_identifier_and_discards_the_data(): void
    {
        $session = $this->createSession();
        $session->set(SessionAuthenticator::AUTHENTICATABLE_KEY, 1);
        $session->set(SessionAuthenticator::AUTHENTICATABLE_CLASS, MemoizedAuthenticatable::class);
        $session->set('key', 'value');
        $sessionManager = new TestingSessionManager();

        $authenticator = new SessionAuthenticator(
            sessionManager: $sessionManager,
            session: $session,
            authenticatableResolver: new CountingAuthenticatableResolver(),
            sessionRegenerator: $this->createRegenerator($session, $sessionManager),
        );

        $authenticator->deauthenticate();

        $this->assertNotSame('test-session', (string) $session->id);
        $this->assertSame(1, $sessionManager->deletedSessions);
        $this->assertSame(1, $sessionManager->savedSessions);
        $this->assertNull($session->get(SessionAuthenticator::AUTHENTICATABLE_KEY));
        $this->assertNull($session->get(SessionAuthenticator::AUTHENTICATABLE_CLASS));
        $this->assertNull($session->get('key'));
    }

    private function createRegenerator(Session $session, ?SessionManager $sessionManager = null): SessionRegenerator
    {
        return new SessionRegenerator(
            sessionManager: $sessionManager ?? new TestingSessionManager(),
            session: $session,
            sessionIdResolver: new TestingSessionIdResolver(),
        );
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

final class TestingSessionIdResolver implements SessionIdResolver
{
    public function resolve(): SessionId
    {
        return new SessionId('test-session');
    }

    public function issueNewId(): SessionId
    {
        return new SessionId('regenerated-session-' . uniqid());
    }
}

final class TestingSessionManager implements SessionManager
{
    public int $savedSessions = 0;

    public int $deletedSessions = 0;

    public function getOrCreate(SessionId $id): Session
    {
        $now = DateTime::now();

        return new Session($id, $now, $now);
    }

    public function save(Session $session): void
    {
        $this->savedSessions++;
    }

    public function delete(Session $session): void
    {
        $this->deletedSessions++;
    }

    public function isValid(Session $session): bool
    {
        return true;
    }

    public function deleteExpiredSessions(): void {}
}
