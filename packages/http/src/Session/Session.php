<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\DateTime\DateTimeInterface;
use Tempest\Support\Random;

use function Tempest\get;

final class Session
{
    public const string VALIDATION_ERRORS = '#validation_errors';

    public const string ORIGINAL_VALUES = '#original_values';

    public const string PREVIOUS_URL = '#previous_url';

    public const string CSRF_TOKEN_KEY = '#csrf_token';

    private array $expiredKeys = [];

    private SessionCache $cache {
        get => get(className:SessionCache::class);
    }

    /**
     * Session token used for cross-site request forgery protection.
     */
    public string $token {
        get {
            if (! $this->get(key:self::CSRF_TOKEN_KEY)) {
                $this->set(key:self::CSRF_TOKEN_KEY, value:Random\uuid());
            }

            return $this->get(key:self::CSRF_TOKEN_KEY);
        }
    }

    public function __construct(
        public SessionId $id,
        public DateTimeInterface $createdAt,
        public DateTimeInterface $lastActiveAt,
        /** @var array<array-key, mixed> */
        public array $data = [],
    ) {}

    public function set(string $key, mixed $value): void
    {
        $this->cache->set(sessionId:$this->id, key:$key, value:$value);
    }

    /**
     * Stores a value in the session that will be available for the next request only.
     */
    public function flash(string $key, mixed $value): void
    {
        $this->cache->set(sessionId:$this->id, key:$key, value:new FlashValue(value:$value));
    }

    /**
     * Reflashes all flash values in the session, making them available for the next request.
     */
    public function reflash(): void
    {
        foreach ($this->cache->all(sessionId:$this->id) as $key => $value) {
            if (! $value instanceof FlashValue) {
                continue;
            }

            unset($this->expiredKeys[$key]);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->cache->get(sessionId:$this->id, key:$key, default:$default);

        if ($value instanceof FlashValue) {
            $this->expiredKeys[$key] = $key;
            $value = $value->value;
        }

        return $value;
    }

    /** @return \Tempest\Validation\Rule[] */
    public function getErrorsFor(string $name): array
    {
        return $this->get(key:self::VALIDATION_ERRORS)[$name] ?? [];
    }

    public function getOriginalValueFor(string $name, mixed $default = ''): mixed
    {
        return $this->get(key:self::ORIGINAL_VALUES)[$name] ?? $default;
    }

    public function getPreviousUrl(): string
    {
        return $this->get(key:self::PREVIOUS_URL, default: '');
    }

    public function setPreviousUrl(string $url): void
    {
        $this->set(key:self::PREVIOUS_URL, value:$url);
    }

    /**
     * Retrieves the value for the given key and removes it from the session.
     */
    public function consume(string $key, mixed $default = null): mixed
    {
        $value = $this->get(key:$key, default:$default);

        $this->remove(key:$key);

        return $value;
    }

    public function all(): array
    {
        return $this->cache->all(sessionId:$this->id);
    }

    public function remove(string $key): void
    {
        $this->cache->remove(sessionId:$this->id, key:$key);
    }

    public function destroy(): void
    {
        $this->cache->destroy(sessionId:$this->id);
    }

    public function isValid(): bool
    {
        return $this->cache->isValid(session:$this->id);
    }

    public function persist(): void
    {
        $this->cache->persist(sessionId:$this->id);
    }

    public function cleanup(): void
    {
        foreach ($this->expiredKeys as $key) {
            $this->cache->remove(sessionId:$this->id, key:$key);
        }
    }
}
