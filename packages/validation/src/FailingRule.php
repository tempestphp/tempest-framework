<?php

namespace Tempest\Validation;

/**
 * Represents a rule that failed during validation, including context about the failure.
 */
final readonly class FailingRule
{
    /**
     * @param Rule $rule The rule that failed validation.
     * @param null|string $field The field name associated with the value that was validated and caused the failure.
     * @param mixed|null $value The value that was validated and caused the failure.
     * @param null|string $key An optional key associated with the value, used for localization.
     */
    public function __construct(
        private(set) Rule $rule,
        private(set) ?string $field = null,
        private(set) mixed $value = null,
        private(set) ?string $key = null,
    ) {}

    /**
     * @param null|string $field The field name associated with the value that was validated and caused the failure.
     */
    public function withField(?string $field): self
    {
        return new self(
            rule: $this->rule,
            field: $field,
            value: $this->value,
            key: $this->key,
        );
    }

    /**
     * @param null|string $key An optional key associated with the value, used for localization.
     */
    public function withKey(?string $key): self
    {
        return new self(
            rule: $this->rule,
            field: $this->field,
            value: $this->value,
            key: $key,
        );
    }

    /**
     * @param null|mixed $value The value that was validated and caused the failure.
     */
    public function withValue(mixed $value): self
    {
        return new self(
            rule: $this->rule,
            field: $this->field,
            value: $value,
            key: $this->key,
        );
    }
}
