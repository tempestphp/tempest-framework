<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\Session\FormSession;
use Tempest\Http\Session\Session;
use Tempest\Validation\FailingRule;
use Tempest\Validation\Rules\HasLength;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class FormSessionTest extends FrameworkIntegrationTestCase
{
    private FormSession $formSession {
        get => $this->container->get(FormSession::class);
    }

    private Session $session {
        get => $this->container->get(Session::class);
    }

    #[Test]
    public function flash_errors_stores_errors(): void
    {
        $errors = [
            'name' => [new FailingRule(new HasLength(min: 3))],
            'email' => [new FailingRule(new HasLength(min: 5))],
        ];

        $this->formSession->setErrors($errors);

        $this->assertEquals($errors, $this->formSession->getErrors());
    }

    #[Test]
    public function errors_for_returns_field_specific_errors(): void
    {
        $nameError = new FailingRule(new HasLength(min: 3));
        $emailError = new FailingRule(new HasLength(min: 5));

        $this->formSession->setErrors([
            'name' => [$nameError],
            'email' => [$emailError],
        ]);

        $this->assertEquals([$nameError], $this->formSession->getErrorsFor('name'));
        $this->assertEquals([$emailError], $this->formSession->getErrorsFor('email'));
    }

    #[Test]
    public function errors_for_returns_empty_array_when_field_has_no_errors(): void
    {
        $this->formSession->setErrors([
            'name' => [new FailingRule(new HasLength(min: 3))],
        ]);

        $this->assertEquals([], $this->formSession->getErrorsFor('email'));
    }

    #[Test]
    public function has_errors_returns_true_when_errors_exist(): void
    {
        $this->formSession->setErrors([
            'name' => [new FailingRule(new HasLength(min: 3))],
        ]);

        $this->assertTrue($this->formSession->hasErrors());
    }

    #[Test]
    public function has_errors_returns_false_when_no_errors(): void
    {
        $this->assertFalse($this->formSession->hasErrors());
    }

    #[Test]
    public function has_error_returns_true_when_field_has_errors(): void
    {
        $this->formSession->setErrors([
            'name' => [new FailingRule(new HasLength(min: 3))],
        ]);

        $this->assertTrue($this->formSession->hasError('name'));
    }

    #[Test]
    public function has_error_returns_false_when_field_has_no_errors(): void
    {
        $this->formSession->setErrors([
            'name' => [new FailingRule(new HasLength(min: 3))],
        ]);

        $this->assertFalse($this->formSession->hasError('email'));
    }

    #[Test]
    public function values_returns_empty_array_when_no_values(): void
    {
        $this->assertEquals([], $this->formSession->values());
    }

    #[Test]
    public function flash_values_stores_values(): void
    {
        $values = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $this->formSession->setOriginalValues($values);

        $this->assertEquals($values, $this->formSession->values());
    }

    #[Test]
    public function value_returns_field_specific_value(): void
    {
        $this->formSession->setOriginalValues([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertEquals('John Doe', $this->formSession->getOriginalValueFor('name'));
        $this->assertEquals('john@example.com', $this->formSession->getOriginalValueFor('email'));
    }

    #[Test]
    public function value_returns_default_when_field_not_found(): void
    {
        $this->formSession->setOriginalValues([
            'name' => 'John Doe',
        ]);

        $this->assertEquals('', $this->formSession->getOriginalValueFor('email'));
        $this->assertEquals('default', $this->formSession->getOriginalValueFor('email', 'default'));
    }

    #[Test]
    public function clear_removes_errors_and_values(): void
    {
        $this->formSession->setErrors([
            'name' => [new FailingRule(new HasLength(min: 3))],
        ]);
        $this->formSession->setOriginalValues([
            'name' => 'John',
        ]);

        $this->formSession->clear();

        $this->assertEquals([], $this->formSession->getErrors());
        $this->assertEquals([], $this->formSession->values());
    }

    #[Test]
    public function errors_are_flashed_and_cleared_after_next_request(): void
    {
        $this->formSession->setErrors([
            'name' => [new FailingRule(new HasLength(min: 3))],
        ]);

        // First access - errors exist
        $this->assertTrue($this->formSession->hasErrors());

        // Simulate cleanup after request
        $this->session->cleanup();

        // Second access - errors cleared
        $this->assertFalse($this->formSession->hasErrors());
    }

    #[Test]
    public function values_are_flashed_and_cleared_after_next_request(): void
    {
        $this->formSession->setOriginalValues([
            'name' => 'John Doe',
        ]);

        // First access - values exist
        $this->assertEquals('John Doe', $this->formSession->getOriginalValueFor('name'));

        // Simulate cleanup after request
        $this->session->cleanup();

        // Second access - values cleared
        $this->assertEquals('', $this->formSession->getOriginalValueFor('name'));
    }
}
