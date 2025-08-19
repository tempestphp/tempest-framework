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

    public const string DEFAULT_ERROR_BAG = 'default';

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
            if (! ($value instanceof FlashValue))
                continue;

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
    public function getErrorsFor(string $name, ?string $bagName = null): array
    {
        $bagName ??= self::DEFAULT_ERROR_BAG;
        $errors = $this->get(self::VALIDATION_ERRORS);

        if ($errors !== null && ! $this->isBaggedStructure($errors)) {
            return $bagName === self::DEFAULT_ERROR_BAG ? ($errors[$name] ?? []) : [];
        }

        return $errors[$bagName][$name] ?? [];
    }

    public function getOriginalValueFor(string $name, mixed $default = '', ?string $bagName = null): mixed
    {
        $bagName ??= self::DEFAULT_ERROR_BAG;
        $values = $this->get(self::ORIGINAL_VALUES);

        if ($values !== null && ! $this->isBaggedStructure($values)) {
            return $bagName === self::DEFAULT_ERROR_BAG ? ($values[$name] ?? $default) : $default;
        }

        return $values[$bagName][$name] ?? $default;
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

    public function flashValidationErrors(array $errors, ?string $bagName = null): void
    {
        $bagName ??= self::DEFAULT_ERROR_BAG;
        $currentErrors = $this->get(self::VALIDATION_ERRORS) ?? [];

        if (! $this->isBaggedStructure($currentErrors)) {
            $currentErrors = empty($currentErrors) ? [] : [self::DEFAULT_ERROR_BAG => $currentErrors];
        }

        $currentErrors[$bagName] = $errors;
        $this->flash(self::VALIDATION_ERRORS, $currentErrors);
    }

    public function flashOriginalValues(array $values, ?string $bagName = null): void
    {
        $bagName ??= self::DEFAULT_ERROR_BAG;
        $currentValues = $this->get(self::ORIGINAL_VALUES) ?? [];

        if (! $this->isBaggedStructure($currentValues)) {
            $currentValues = empty($currentValues) ? [] : [self::DEFAULT_ERROR_BAG => $currentValues];
        }

        $currentValues[$bagName] = $values;
        $this->flash(self::ORIGINAL_VALUES, $currentValues);
    }

    public function getAllErrors(?string $bagName = null): array
    {
        $bagName ??= self::DEFAULT_ERROR_BAG;
        $errors = $this->get(self::VALIDATION_ERRORS);

        if ($errors !== null && ! $this->isBaggedStructure($errors)) {
            return $bagName === self::DEFAULT_ERROR_BAG ? $errors : [];
        }

        return $errors[$bagName] ?? [];
    }

    public function clearErrors(?string $bagName = null): void
    {
        $bagName ??= self::DEFAULT_ERROR_BAG;
        $errors = $this->get(self::VALIDATION_ERRORS) ?? [];

        if ($this->isBaggedStructure($errors)) {
            unset($errors[$bagName]);
            if (empty($errors)) {
                $this->remove(self::VALIDATION_ERRORS);
            } else {
                $this->set(self::VALIDATION_ERRORS, $errors);
            }
        } elseif ($bagName === self::DEFAULT_ERROR_BAG) {
            $this->remove(self::VALIDATION_ERRORS);
        }
    }

    private function isBaggedStructure(array $data): bool
    {
        if (empty($data)) {
            return false; // Empty arrays are considered non-bagged for backward compatibility
        }

        // Check if this looks like a bagged structure:
        // - Bagged: ['default' => ['field' => [...]], 'other' => ['field' => [...]]]
        // - Old: ['field' => [...], 'field2' => [...]]

        // A bagged structure should have at least one known bag name as key
        // or all values should be arrays of arrays (not arrays of objects)
        foreach ($data as $key => $value) {
            if ($key === self::DEFAULT_ERROR_BAG) {
                return true;
            }

            // If value is not an array, it's definitely not bagged
            if (! is_array($value)) {
                return false;
            }

            // Check if the value contains objects (old structure for validation errors)
            // Old structure: ['field' => [Rule, Rule]]
            // New structure: ['bag' => ['field' => [Rule, Rule]]]
            foreach ($value as $item) {
                if (is_object($item)) {
                    // Contains objects directly, so it's the old structure
                    return false;
                }
            }
        }

        // If all values are arrays of arrays (no objects), it's likely bagged
        return true;
    }
}
