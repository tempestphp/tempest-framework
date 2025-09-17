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

    private SessionManager $manager {
        get => get(SessionManager::class);
    }

    /**
     * Session token used for cross-site request forgery protection.
     */
    public string $token {
        get {
            if (! $this->get(self::CSRF_TOKEN_KEY)) {
                $this->set(self::CSRF_TOKEN_KEY, Random\uuid());
            }

            return $this->get(self::CSRF_TOKEN_KEY);
        }
    }

    public function __construct(
        public SessionId $id,
        public DateTimeInterface $createdAt,
        /** @var array<array-key, mixed> */
        public array $data = [],
    ) {}

    public function set(string $key, mixed $value): void
    {
        $this->manager->set($this->id, $key, $value);
    }

    /**
     * Stores a value in the session that will be available for the next request only.
     */
    public function flash(string $key, mixed $value): void
    {
        $this->manager->set($this->id, $key, new FlashValue($value));
    }

    /**
     * Reflashes all flash values in the session, making them available for the next request.
     */
    public function reflash(): void
    {
        foreach ($this->manager->all($this->id) as $key => $value) {
            if (! $value instanceof FlashValue) {
                continue;
            }

            unset($this->expiredKeys[$key]);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->manager->get($this->id, $key, $default);

        if ($value instanceof FlashValue) {
            $this->expiredKeys[$key] = $key;
            $value = $value->value;
        }

        return $value;
    }

    /** @return \Tempest\Validation\Rule[] */
    public function getErrorsFor(string $name): array
    {
        return $this->get(self::VALIDATION_ERRORS)[$name] ?? [];
    }

    public function getOriginalValueFor(string $name, mixed $default = ''): mixed
    {
        return $this->get(self::ORIGINAL_VALUES)[$name] ?? $default;
    }

    public function getPreviousUrl(): string
    {
        return $this->get(self::PREVIOUS_URL, default: '');
    }

    public function setPreviousUrl(string $url): void
    {
        $this->set(self::PREVIOUS_URL, $url);
    }

    /**
     * Retrieves the value for the given key and removes it from the session.
     */
    public function consume(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);

        $this->remove($key);

        return $value;
    }

    public function all(): array
    {
        return $this->manager->all($this->id);
    }

    public function remove(string $key): void
    {
        $this->manager->remove($this->id, $key);
    }

    public function destroy(): void
    {
        $this->manager->destroy($this->id);
    }

    public function isValid(): bool
    {
        return $this->manager->isValid($this->id);
    }

    public function cleanup(): void
    {
        foreach ($this->expiredKeys as $key) {
            $this->manager->remove($this->id, $key);
        }
    }
}
