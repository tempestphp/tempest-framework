<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\DateTime\DateTimeInterface;

use function Tempest\get;

final class Session
{
    public const string VALIDATION_ERRORS = 'validation_errors';

    public const string ORIGINAL_VALUES = 'original_values';

    public const string PREVIOUS_URL = '_previous_url';

    private array $expiredKeys = [];

    public function __construct(
        public SessionId $id,
        public DateTimeInterface $createdAt,
        /** @var array<array-key, mixed> */
        public array $data = [],
    ) {}

    public function set(string $key, mixed $value): void
    {
        $this->getSessionManager()->set($this->id, $key, $value);
    }

    public function flash(string $key, mixed $value): void
    {
        $this->getSessionManager()->set($this->id, $key, new FlashValue($value));
    }

    public function reflash(): void
    {
        foreach ($this->getSessionManager()->all($this->id) as $key => $value) {
            if (! ($value instanceof FlashValue))
                continue;

            unset($this->expiredKeys[$key]);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->getSessionManager()->get($this->id, $key, $default);

        if ($value instanceof FlashValue) {
            $this->expiredKeys[$key] = $key;
            $value = $value->value;
        }

        return $value;
    }

    public function getPreviousUrl(): string
    {
        return $this->get(self::PREVIOUS_URL, '');
    }

    public function setPreviousUrl(string $url): void
    {
        $this->set(self::PREVIOUS_URL, $url);
    }

    public function consume(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);

        $this->remove($key);

        return $value;
    }

    public function all(): array
    {
        return $this->getSessionManager()->all($this->id);
    }

    public function remove(string $key): void
    {
        $this->getSessionManager()->remove($this->id, $key);
    }

    public function destroy(): void
    {
        $this->getSessionManager()->destroy($this->id);
    }

    public function isValid(): bool
    {
        return $this->getSessionManager()->isValid($this->id);
    }

    public function cleanup(): void
    {
        foreach ($this->expiredKeys as $key) {
            $this->getSessionManager()->remove($this->id, $key);
        }
    }

    private function getSessionManager(): SessionManager
    {
        return get(SessionManager::class);
    }
}
