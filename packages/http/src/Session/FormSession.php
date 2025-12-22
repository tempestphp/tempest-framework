<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\Container\Singleton;
use Tempest\Validation\FailingRule;

/**
 * Manages form validation errors and original input values in the session.
 */
#[Singleton]
final readonly class FormSession
{
    private const string VALIDATION_ERRORS_KEY = '#validation_errors';
    private const string ORIGINAL_VALUES_KEY = '#original_values';

    public function __construct(
        private Session $session,
    ) {}

    /**
     * Stores validation errors for the next request.
     *
     * @param array<string,FailingRule[]> $errors
     */
    public function setErrors(array $errors): void
    {
        $this->session->flash(self::VALIDATION_ERRORS_KEY, $errors);
    }

    /**
     * Gets all validation errors.
     *
     * @return array<string,FailingRule[]>
     */
    public function getErrors(): array
    {
        return $this->session->get(self::VALIDATION_ERRORS_KEY, []);
    }

    /**
     * Gets validation errors for a specific field.
     *
     * @return FailingRule[]
     */
    public function getErrorsFor(string $field): array
    {
        return $this->getErrors()[$field] ?? [];
    }

    /**
     * Checks if there are any validation errors.
     */
    public function hasErrors(): bool
    {
        return $this->getErrors() !== [];
    }

    /**
     * Checks if a specific field has validation errors.
     */
    public function hasError(string $field): bool
    {
        return $this->getErrorsFor($field) !== [];
    }

    /**
     * Stores each field's original form values for the next request.
     *
     * @param array<string,mixed> $values
     */
    public function setOriginalValues(array $values): void
    {
        $this->session->flash(self::ORIGINAL_VALUES_KEY, $values);
    }

    /**
     * Gets all original form values. The keys are the form fields.
     *
     * @return array<string,mixed>
     */
    public function values(): array
    {
        return $this->session->get(self::ORIGINAL_VALUES_KEY, []);
    }

    /**
     * Gets the original value for a specific field.
     */
    public function getOriginalValueFor(string $field, mixed $default = null): mixed
    {
        return $this->values()[$field] ?? $default;
    }

    /**
     * Clears all validation errors and original values.
     */
    public function clear(): void
    {
        $this->session->remove(self::VALIDATION_ERRORS_KEY);
        $this->session->remove(self::ORIGINAL_VALUES_KEY);
    }
}
